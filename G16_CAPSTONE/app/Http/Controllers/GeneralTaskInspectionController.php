<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\GeneratedSchedule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class GeneralTaskInspectionController extends Controller
{
    private const STATUS_OPTIONS = ['pending', 'completed'];

    public function index(Request $request): View|RedirectResponse
    {
        $user = auth()->user();
        if (!$user || !in_array($user->user_role, ['inspector', 'educator'])) {
            return redirect()->route('generalTask')->with('error', 'Only educators or inspectors can access the General Task Inspection board.');
        }

        $filters = $this->prepareFilters($request);

        $categories = Category::query()
            ->whereNotNull('parent_id')
            ->orderBy('name')
            ->pluck('name')
            ->unique()
            ->values();

        $schedules = $this->fetchSchedules($filters);
        $summary = $this->buildSummary($schedules);
        $batches = $this->groupByBatch($schedules);
        $statusOptions = $this->statusOptions();

        return view('generalTaskInspection', [
            'filters' => $filters,
            'categories' => $categories,
            'statusOptions' => $statusOptions,
            'summary' => $summary,
            'batches' => $batches,
            'scheduleCount' => $schedules->count(),
            'inspectorName' => trim(($user->user_fname ?? '') . ' ' . ($user->user_lname ?? '')) ?: ($user->name ?? 'Inspector'),
        ]);
    }

    public function updateStatus(Request $request, GeneratedSchedule $generatedSchedule): RedirectResponse
    {
        $user = auth()->user();
        if (!$user || $user->user_role !== 'inspector') {
            return redirect()->route('generalTask')->with('error', 'Only inspectors can update task statuses.');
        }

        $data = $request->validate([
            'task_status' => 'required|in:' . implode(',', self::STATUS_OPTIONS),
            'redirect' => 'nullable|string',
        ]);

        $generatedSchedule->update(['task_status' => $data['task_status']]);

        $redirectUrl = $data['redirect'] ?? route('generalTask.inspection');

        return redirect($redirectUrl)->with('success', 'Task status updated successfully.');
    }

    public function statusDetails(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user || !in_array($user->user_role, ['inspector', 'educator'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only educators or inspectors can view task details.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', self::STATUS_OPTIONS),
            'modal_category' => 'nullable|string',
            'date_filter' => 'nullable|date',
        ]);

        $filters = $this->prepareFilters($request);
        $filters['status'] = $validated['status'];

        $schedules = $this->fetchSchedules($filters);

        if (!empty($validated['modal_category']) && $validated['modal_category'] !== 'all') {
            $schedules = $schedules->where('category_name', $validated['modal_category']);
        }

        if ($request->filled('date_filter')) {
            try {
                $dateFilter = Carbon::parse($request->input('date_filter'))->toDateString();
                $schedules = $schedules->filter(function ($schedule) use ($dateFilter) {
                    $date = optional($schedule->schedule_date)->toDateString();
                    return $date === $dateFilter;
                })->values();
            } catch (\Throwable) {
                // Ignore invalid date filter and keep original collection
            }
        }

        $categoryNames = $schedules->pluck('category_name')->filter()->unique()->values();
        $categoryRecords = $categoryNames->isEmpty()
            ? collect()
            : Category::query()
                ->whereIn('name', $categoryNames)
                ->with('parentCategory')
                ->get()
                ->keyBy('name');

        $students = $schedules->map(function ($schedule) use ($categoryRecords) {
            $categoryName = $schedule->category_name ?? 'General Task';
            $category = $categoryRecords->get($categoryName);
            $hasParent = $category && $category->parentCategory;
            $mainArea = $hasParent ? $category->parentCategory->name : ($category->name ?? 'General Task');
            $subArea = $hasParent ? $category->name : null;
            $normalizedStatus = $this->normalizeStatus($schedule->task_status);
            $meta = $this->statusMeta($normalizedStatus);

            return [
                'id' => $schedule->id,
                'student_name' => $schedule->student_name ?? 'Unassigned',
                'initials' => $this->initials($schedule->student_name),
                'batch' => $schedule->batch ?? 'Unspecified',
                'main_area' => $mainArea,
                'sub_area' => $subArea,
                'category_name' => $categoryName,
                'task_title' => $schedule->task_title ?? 'Task',
                'task_description' => $schedule->task_description,
                'progress' => $meta['progress'],
                'status_key' => $meta['key'],
                'status_label' => $meta['label'],
                'status_color' => $meta['color'],
                'status_bg' => $meta['bg'],
                'schedule_date' => optional($schedule->schedule_date)->toDateString(),
                'schedule_date_formatted' => optional($schedule->schedule_date)->format('M d, Y') ?? 'No schedule date',
            ];
        })->values();

        $categoryOptions = $categoryNames->map(function ($name) use ($categoryRecords) {
            $record = $categoryRecords->get($name);
            $hasParent = $record && $record->parentCategory;

            return [
                'value' => $name,
                'label' => $hasParent
                    ? $record->parentCategory->name . ' • ' . $record->name
                    : $name,
                'main_area' => $hasParent ? $record->parentCategory->name : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'status' => $validated['status'],
            'meta' => $this->statusMeta($validated['status']),
            'total' => $students->count(),
            'students' => $students,
            'categories' => $categoryOptions,
        ]);
    }

    private function prepareFilters(Request $request): array
    {
        $dateRange = $request->input('date_range', 'today');
        $customStart = $request->input('start_date');
        $customEnd = $request->input('end_date');
        [$startDate, $endDate, $label] = $this->resolveDateRange($dateRange, $customStart, $customEnd);

        return [
            'category' => $request->input('category', 'all'),
            'status' => $request->input('status', 'all'),
            'search' => trim((string)$request->input('search')),
            'date_range' => $dateRange,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'date_label' => $label,
            'custom_start' => $customStart,
            'custom_end' => $customEnd,
            'raw_date_range' => $dateRange,
        ];
    }

    private function fetchSchedules(array $filters): Collection
    {
        return GeneratedSchedule::query()
            ->select('id', 'category_name', 'schedule_date', 'student_name', 'task_title', 'task_description', 'batch', 'task_status')
            ->when($filters['category'] !== 'all', function ($query) use ($filters) {
                $query->where('category_name', $filters['category']);
            })
            ->when($filters['status'] !== 'all', function ($query) use ($filters) {
                if ($filters['status'] === 'pending') {
                    $query->where(function ($inner) {
                        $inner->whereNull('task_status')
                            ->orWhereIn('task_status', ['pending', 'in_progress']);
                    });
                } else {
                    $query->where('task_status', $filters['status']);
                }
            })
            ->when($filters['search'], function ($query) use ($filters) {
                $search = '%' . $filters['search'] . '%';
                $query->where(function ($inner) use ($search) {
                    $inner->where('student_name', 'like', $search)
                        ->orWhere('task_title', 'like', $search)
                        ->orWhere('category_name', 'like', $search);
                });
            })
            ->when(($filters['date_range'] ?? 'today') !== 'all', function ($query) use ($filters) {
                $query->whereBetween('schedule_date', [$filters['start_date'], $filters['end_date']]);
            })
            ->orderBy('schedule_date')
            ->orderBy('category_name')
            ->get();
    }

    private function resolveDateRange(string $dateRange, ?string $customStart, ?string $customEnd): array
    {
        $today = Carbon::today();
        return match ($dateRange) {
            'all' => [
                Carbon::create(2000, 1, 1)->startOfDay(),
                $today->copy()->endOfDay(),
                'All time',
            ],
            'week' => [
                $today->copy()->startOfWeek(Carbon::MONDAY),
                $today->copy()->endOfWeek(Carbon::MONDAY),
                sprintf('%s - %s', $today->copy()->startOfWeek(Carbon::MONDAY)->format('M d'), $today->copy()->endOfWeek(Carbon::MONDAY)->format('M d, Y')),
            ],
            'custom' => $this->customDateRange($customStart, $customEnd, $today),
            default => [$today, $today, $today->format('l, F j, Y')],
        };
    }

    private function customDateRange(?string $start, ?string $end, Carbon $fallback): array
    {
        try {
            $startDate = $start ? Carbon::parse($start)->startOfDay() : $fallback->copy();
        } catch (\Throwable) {
            $startDate = $fallback->copy();
        }

        try {
            $endDate = $end ? Carbon::parse($end)->endOfDay() : $startDate->copy();
        } catch (\Throwable) {
            $endDate = $startDate->copy();
        }

        if ($endDate->lessThan($startDate)) {
            [$startDate, $endDate] = [$endDate->copy(), $startDate->copy()];
        }

        return [$startDate, $endDate, sprintf('%s - %s', $startDate->format('M d'), $endDate->format('M d, Y'))];
    }

    private function buildSummary(Collection $schedules): array
    {
        $statusCounts = [
            'pending' => 0,
            'completed' => 0,
        ];

        foreach ($schedules as $schedule) {
            $key = $this->normalizeStatus($schedule->task_status);
            $statusCounts[$key] = ($statusCounts[$key] ?? 0) + 1;
        }

        $total = $schedules->count();
        $today = Carbon::today()->toDateString();

        return [
            'total' => $total,
            'statuses' => $statusCounts,
            'due_today' => $schedules->where('schedule_date', $today)->count(),
            'completed_percent' => $total ? round(($statusCounts['completed'] / $total) * 100) : 0,
            'pending_percent' => $total ? round(($statusCounts['pending'] / $total) * 100) : 0,
        ];
    }

    private function groupByBatch(Collection $schedules): array
    {
        return $schedules->groupBy(function ($schedule) {
            return $this->formatBatchLabel($schedule->batch);
        })->map(function ($items, $label) {
            return [
                'label' => $label,
                'items' => $items->map(function ($schedule) {
                    return [
                        'id' => $schedule->id,
                        'category' => $schedule->category_name ?? 'General Task',
                        'student' => $schedule->student_name ?? 'Unassigned',
                        'task' => $schedule->task_title ?? 'Task',
                        'description' => $schedule->task_description,
                        'date' => $schedule->schedule_date ? Carbon::parse($schedule->schedule_date)->format('l, F j, Y') : 'No schedule date',
                        'raw_date' => optional($schedule->schedule_date)->toDateString(),
                        'batch' => $schedule->batch,
                        'task_status' => $this->normalizeStatus($schedule->task_status),
                    ];
                })->values(),
            ];
        })->values()->toArray();
    }

    private function formatBatchLabel(?string $batch): string
    {
        if (!$batch) {
            return 'Unspecified Batch';
        }

        if (preg_match('/(20\\d{2})/', $batch, $matches)) {
            return 'Class ' . $matches[1];
        }

        if (str_contains($batch, 'BATCH_')) {
            return 'Class ' . preg_replace('/^BATCH_/i', '', $batch);
        }

        return ucfirst(trim($batch));
    }

    private function statusOptions(): array
    {
        return [
            'all' => 'All Status',
            'pending' => 'Incomplete',
            'completed' => 'Completed',
        ];
    }

    private function statusMeta(?string $status): array
    {
        $map = [
            'pending' => ['key' => 'pending', 'label' => 'Incomplete', 'color' => '#f97316', 'bg' => '#fef3c7', 'progress' => 50],
            'completed' => ['key' => 'completed', 'label' => 'Completed', 'color' => '#16a34a', 'bg' => '#dcfce7', 'progress' => 100],
        ];

        return $map[$this->normalizeStatus($status)] ?? $map['pending'];
    }

    private function normalizeStatus(?string $status): string
    {
        return $status === 'completed' ? 'completed' : 'pending';
    }

    private function initials(?string $name): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return 'NA';
        }

        $parts = preg_split('/\s+/', $name);
        $first = $parts[0] ?? '';
        $last = $parts[count($parts) - 1] ?? '';

        $initials = mb_strtoupper(mb_substr($first, 0, 1) . ($last ? mb_substr($last, 0, 1) : ''));

        return $initials ?: mb_strtoupper(mb_substr($name, 0, 2));
    }
}
