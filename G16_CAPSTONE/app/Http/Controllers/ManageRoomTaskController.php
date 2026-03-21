<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\TaskTemplate; // <-- ensure model exists and is autoloadable

class ManageRoomTaskController extends Controller
{
    /**
     * Apply one or more manage tasks to a room and persist to existing `roomtask` table.
     * Expects JSON: { tasks: [ { title, description, room_number, area?, day? }, ... ] }
     */
    public function apply(Request $request)
    {
        $data = $request->validate([
            'tasks' => 'required|array|min:1',
            'tasks.*.title' => 'required|string|max:255',
            'tasks.*.description' => 'nullable|string',
            'tasks.*.room_number' => 'required|string',
        ]);

        $now = Carbon::now();
        $daysOfWeek = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        $inserted = 0;
        $skipped = 0;

        foreach ($data['tasks'] as $t) {
            $title = $t['title'] ?? '';
            $desc = $t['description'] ?? '';
            $room = $t['room_number'] ?? '';

            // Determine target days
            $targetDays = [];
            if (!empty($t['day'])) {
                if (is_array($t['day'])) $targetDays = $t['day'];
                else $targetDays = [(string)$t['day']];
            } else {
                $targetDays = $daysOfWeek;
            }

            // Ensure a TaskTemplate exists and get its id — do this once per task so we can link roomtask rows to the template
            $templateId = null;
            try {
                $areaVal = $title ?: ($t['area'] ?? '');
                $descVal = $desc;
                if (trim($areaVal) !== '') {
                    $tpl = TaskTemplate::firstOrCreate(
                        ['area' => $areaVal, 'description' => $descVal],
                        ['is_fixed' => false, 'is_active' => true]
                    );
                    $templateId = $tpl->id ?? null;
                }
            } catch (\Throwable $e) {
                // If template persistence fails, continue — we still insert roomtask rows without template link
                \Log::warning('Failed to create/find TaskTemplate: '.$e->getMessage());
                $templateId = null;
            }

            foreach ($targetDays as $day) {
                $day = (string)$day;

                try {
                    $existsQuery = DB::table('roomtask')
                        ->where('room_number', $room)
                        ->where('name', '')
                        ->where('desc', $desc)
                        ->where('day', $day);

                    // if templateId present, prefer to use it for dedupe
                    if ($templateId) $existsQuery = $existsQuery->where('task_template_id', $templateId);

                    $exists = $existsQuery->exists();
                } catch (\Throwable $e) {
                    try {
                        $existsQuery = DB::connection('login')->table('roomtask')
                            ->where('room_number', $room)
                            ->where('name', '')
                            ->where('desc', $desc)
                            ->where('day', $day);
                        if ($templateId) $existsQuery = $existsQuery->where('task_template_id', $templateId);
                        $exists = $existsQuery->exists();
                    } catch (\Throwable $ee) {
                        $exists = false;
                    }
                }

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $row = [
                    'name' => '',
                    'room_number' => $room,
                    'area' => $title ?? ($t['area'] ?? ''),
                    'desc' => $desc,
                    'day' => $day,
                    'status' => $t['status'] ?? 'not yet',
                    'week' => $t['week'] ?? null,
                    'month' => $t['month'] ?? null,
                    'year' => $t['year'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                // attach template id when available
                if ($templateId) $row['task_template_id'] = $templateId;

                // Try insertion; if the column task_template_id doesn't exist on that connection, retry without it.
                try {
                    DB::table('roomtask')->insert($row);
                } catch (\Throwable $e) {
                    // if insertion failed because of unknown column, remove task_template_id and retry
                    try {
                        if (isset($row['task_template_id'])) unset($row['task_template_id']);
                        DB::table('roomtask')->insert($row);
                    } catch (\Throwable $ee) {
                        // fallback to login connection
                        try {
                            DB::connection('login')->table('roomtask')->insert($row);
                        } catch (\Throwable $eee) {
                            \Log::error('Failed to insert roomtask on both default and login connections: ' . $eee->getMessage());
                            continue;
                        }
                    }
                }

                $inserted++;
            }
        }

        return response()->json([
            'success' => true,
            'inserted' => $inserted,
            'skipped' => $skipped,
        ]);
    }

    /**
     * Persist a new TaskTemplate created in Manage Room Tasks UI.
     * Expects JSON { title: string, description?: string }
     */
    public function storeTemplate(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $area = $data['title'];
        $desc = $data['description'] ?? '';

        try {
            $template = TaskTemplate::firstOrCreate(
                ['area' => $area, 'description' => $desc],
                ['is_fixed' => false, 'is_active' => true]
            );

            return response()->json(['success' => true, 'template' => $template]);
        } catch (\Throwable $e) {
            \Log::error('Failed to store TaskTemplate: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to save template'], 500);
        }
    }

    /**
     * Delete all applied tasks (server-side) that were created from TaskTemplate(s).
     * This is the server action the Manage Room Tasks "Delete All Applied Tasks" UI should call.
     */
    public function deleteAllApplied(Request $request)
    {
        $deletedTotal = 0;

        try {
            // First, delete rows that have a task_template_id (most reliable)
            try {
                $deleted = DB::table('roomtask')->whereNotNull('task_template_id')->delete();
                $deletedTotal += $deleted;
            } catch (\Throwable $e) {
                // fallback to login connection
                try {
                    $deleted = DB::connection('login')->table('roomtask')->whereNotNull('task_template_id')->delete();
                    $deletedTotal += $deleted;
                } catch (\Throwable $ee) {
                    \Log::warning('deleteAllApplied primary delete failed: '.$ee->getMessage());
                }
            }

            // Additionally, for installations without task_template_id, remove rows that appear to be template applications:
            // (no week/month/year) AND name empty AND area matches a TaskTemplate area
            $templateAreas = TaskTemplate::pluck('area')->filter()->map(function($v){ return (string)$v; })->toArray();
            if (!empty($templateAreas)) {
                try {
                    $q = DB::table('roomtask')->whereNull('week')->whereNull('month')->whereNull('year')->where('name', '');
                    $q = $q->whereIn('area', $templateAreas);
                    $deleted2 = $q->delete();
                    $deletedTotal += $deleted2;
                } catch (\Throwable $e) {
                    try {
                        $q = DB::connection('login')->table('roomtask')->whereNull('week')->whereNull('month')->whereNull('year')->where('name', '');
                        $q = $q->whereIn('area', $templateAreas);
                        $deleted2 = $q->delete();
                        $deletedTotal += $deleted2;
                    } catch (\Throwable $ee) {
                        \Log::warning('deleteAllApplied secondary delete failed: '.$ee->getMessage());
                    }
                }
            }

            return response()->json(['success' => true, 'deleted' => $deletedTotal]);
        } catch (\Throwable $e) {
            \Log::error('deleteAllApplied failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete applied tasks'], 500);
        }
    }

    /**
     * Mark task template(s) as inactive so they can no longer be applied from Manage Room Tasks.
     * Expects JSON: { tasks: [ { title, description }, ... ] }
     */
    public function deleteTemplate(Request $request)
    {
        $data = $request->validate([
            'tasks' => 'required|array|min:1',
            'tasks.*.title' => 'nullable|string|max:255',
            'tasks.*.description' => 'nullable|string',
        ]);

        $processed = 0;
        foreach ($data['tasks'] as $t) {
            $area = trim((string)($t['title'] ?? ''));
            $desc = trim((string)($t['description'] ?? ''));

            if ($area === '' && $desc === '') {
                continue;
            }

            try {
                $query = \App\Models\TaskTemplate::query();
                if ($area !== '') $query->where('area', $area);
                if ($desc !== '') $query->where('description', $desc);

                $templates = $query->get();
                foreach ($templates as $tpl) {
                    $tpl->is_active = false;
                    $tpl->save();
                    $processed++;
                }
            } catch (\Throwable $e) {
                \Log::warning('Failed to mark TaskTemplate inactive: ' . $e->getMessage());
            }

            // Also remove any base/template rows in the roomtask table (rows with no week/month/year)
            try {
                $roomtaskQuery = \DB::table('roomtask')->whereNull('week')->whereNull('month')->whereNull('year');
                if ($area !== '') $roomtaskQuery->where('area', $area);
                if ($desc !== '') $roomtaskQuery->where('desc', $desc);
                $roomtaskQuery->delete();
            } catch (\Throwable $e) {
                // Try login connection if default fails
                try {
                    $roomtaskQuery = \DB::connection('login')->table('roomtask')->whereNull('week')->whereNull('month')->whereNull('year');
                    if ($area !== '') $roomtaskQuery->where('area', $area);
                    if ($desc !== '') $roomtaskQuery->where('desc', $desc);
                    $roomtaskQuery->delete();
                } catch (\Throwable $ee) {
                    \Log::warning('Failed to delete roomtask template rows when deleting manage task: ' . $ee->getMessage());
                }
            }
        }

        return response()->json(['success' => true, 'processed' => $processed]);
    }
}
