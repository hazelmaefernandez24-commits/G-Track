<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ErrorPreventionService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class SystemHealthCheck extends Command
{
    protected $signature = 'system:health-check {--fix : Attempt to fix found issues}';
    protected $description = 'Perform comprehensive system health check and optionally fix issues';

    public function handle()
    {
        $this->info('🔍 Starting System Health Check...');
        $this->newLine();

        $issues = [];
        $fixes = [];

        // Check 1: Database Tables
        $this->info('📋 Checking Database Tables...');
        $tableIssues = $this->checkTables();
        if (!empty($tableIssues)) {
            $issues['tables'] = $tableIssues;
            $this->warn('❌ Found table issues: ' . count($tableIssues));
        } else {
            $this->info('✅ All required tables exist');
        }

        // Check 1.5: Database Columns
        $this->info('📋 Checking Database Columns...');
        $columnIssues = $this->checkColumns();
        if (!empty($columnIssues)) {
            $issues['columns'] = $columnIssues;
            $this->warn('❌ Found column issues: ' . count($columnIssues));
        } else {
            $this->info('✅ All required columns exist');
        }

        // Check 2: Model Methods
        $this->info('🔧 Checking Model Methods...');
        $methodIssues = $this->checkModelMethods();
        if (!empty($methodIssues)) {
            $issues['methods'] = $methodIssues;
            $this->warn('❌ Found method issues: ' . count($methodIssues));
        } else {
            $this->info('✅ All required methods exist');
        }

        // Check 3: Routes
        $this->info('🛣️ Checking Routes...');
        $routeIssues = $this->checkRoutes();
        if (!empty($routeIssues)) {
            $issues['routes'] = $routeIssues;
            $this->warn('❌ Found route issues: ' . count($routeIssues));
        } else {
            $this->info('✅ All critical routes are defined');
        }

        // Check 4: Controllers
        $this->info('🎮 Checking Controllers...');
        $controllerIssues = $this->checkControllers();
        if (!empty($controllerIssues)) {
            $issues['controllers'] = $controllerIssues;
            $this->warn('❌ Found controller issues: ' . count($controllerIssues));
        } else {
            $this->info('✅ All controllers exist');
        }

        // Check 5: Views
        $this->info('👁️ Checking Views...');
        $viewIssues = $this->checkViews();
        if (!empty($viewIssues)) {
            $issues['views'] = $viewIssues;
            $this->warn('❌ Found view issues: ' . count($viewIssues));
        } else {
            $this->info('✅ All critical views exist');
        }

        // Summary
        $this->newLine();
        if (empty($issues)) {
            $this->info('🎉 System Health Check PASSED - No issues found!');
        } else {
            $this->error('⚠️ System Health Check FAILED - Issues found:');
            foreach ($issues as $category => $categoryIssues) {
                $this->error("  {$category}: " . count($categoryIssues) . ' issues');
                foreach ($categoryIssues as $issue) {
                    $this->line("    - {$issue}");
                }
            }

            if ($this->option('fix')) {
                $this->newLine();
                $this->info('🔧 Attempting to fix issues...');
                $this->attemptFixes($issues);
            } else {
                $this->newLine();
                $this->info('💡 Run with --fix option to attempt automatic fixes');
            }
        }

        return empty($issues) ? 0 : 1;
    }

    private function checkTables()
    {
        $requiredTables = [
            'users', 'meals', 'feedback', 'kitchen_menu_polls',
            'notifications', 'pre_orders', 'post_assessments'
        ];

        $issues = [];
        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                $issues[] = "Missing table: {$table}";
            }
        }

        return $issues;
    }

    private function checkColumns()
    {
        $requiredColumns = [
            'feedback' => ['student_id', 'meal_type', 'meal_date', 'rating', 'comments', 'suggestions', 'food_quality', 'dietary_concerns', 'is_anonymous'],
            'meals' => ['name', 'ingredients', 'meal_type', 'day_of_week', 'week_cycle'],
            'kitchen_menu_polls' => ['meal_name', 'meal_type', 'poll_date', 'deadline', 'status'],
            'users' => ['name', 'email', 'role']
        ];

        $issues = [];
        foreach ($requiredColumns as $table => $columns) {
            if (!Schema::hasTable($table)) {
                $issues[] = "Table missing for column check: {$table}";
                continue;
            }

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    $issues[] = "Missing column: {$table}.{$column}";
                }
            }
        }

        return $issues;
    }

    private function checkModelMethods()
    {
        $checks = [
            'App\Models\Meal' => ['forWeekCycle', 'forDay'],
            'App\Models\User' => ['hasRole'],
            'App\Models\KitchenMenuPoll' => ['isActive']
        ];

        $issues = [];
        foreach ($checks as $model => $methods) {
            if (!class_exists($model)) {
                $issues[] = "Missing model: {$model}";
                continue;
            }

            foreach ($methods as $method) {
                if (!method_exists($model, $method)) {
                    $issues[] = "Missing method: {$model}::{$method}";
                }
            }
        }

        return $issues;
    }

    private function checkRoutes()
    {
        $criticalRoutes = [
            'cook.menu', 'kitchen.daily-menu', 'student.menu',
            'cook.dashboard', 'kitchen.dashboard', 'student.dashboard'
        ];

        $issues = [];
        $routes = collect(Route::getRoutes())->map(function ($route) {
            return $route->getName();
        })->filter()->toArray();

        foreach ($criticalRoutes as $routeName) {
            if (!in_array($routeName, $routes)) {
                $issues[] = "Missing route: {$routeName}";
            }
        }

        return $issues;
    }

    private function checkControllers()
    {
        $controllers = [
            'App\Http\Controllers\Cook\MenuController',
            'App\Http\Controllers\Kitchen\MenuController',
            'App\Http\Controllers\Student\MenuController',
            'App\Http\Controllers\Cook\DashboardController',
            'App\Http\Controllers\Kitchen\DashboardController',
            'App\Http\Controllers\Student\DashboardController'
        ];

        $issues = [];
        foreach ($controllers as $controller) {
            if (!class_exists($controller)) {
                $issues[] = "Missing controller: {$controller}";
            }
        }

        return $issues;
    }

    private function checkViews()
    {
        $views = [
            'cook.menu', 'cook.dashboard',
            'kitchen.daily-menu', 'kitchen.dashboard',
            'student.menu', 'student.dashboard'
        ];

        $issues = [];
        foreach ($views as $view) {
            $viewPath = resource_path("views/{$view}.blade.php");
            if (!file_exists($viewPath)) {
                $issues[] = "Missing view: {$view}";
            }
        }

        return $issues;
    }

    private function attemptFixes($issues)
    {
        $fixed = 0;

        // Fix missing tables
        if (isset($issues['tables'])) {
            $this->info('🔧 Attempting to fix table issues...');
            foreach ($issues['tables'] as $issue) {
                if (strpos($issue, 'Missing table:') === 0) {
                    $table = str_replace('Missing table: ', '', $issue);
                    if ($this->createMissingTable($table)) {
                        $this->info("✅ Created table: {$table}");
                        $fixed++;
                    }
                }
            }
        }

        $this->newLine();
        $this->info("🎯 Fixed {$fixed} issues automatically");
        
        if ($fixed < array_sum(array_map('count', $issues))) {
            $this->warn('⚠️ Some issues require manual intervention');
        }
    }

    private function createMissingTable($table)
    {
        try {
            switch ($table) {
                case 'feedback':
                    Schema::create('feedback', function ($table) {
                        $table->id();
                        $table->foreignId('student_id')->constrained('users');
                        $table->string('meal_type');
                        $table->date('meal_date');
                        $table->integer('rating');
                        $table->json('food_quality');
                        $table->text('comments')->nullable();
                        $table->text('suggestions')->nullable();
                        $table->json('dietary_concerns')->nullable();
                        $table->boolean('is_anonymous')->default(false);
                        $table->string('meal_name')->nullable();
                        $table->foreignId('meal_id')->nullable()->constrained('meals');
                        $table->timestamps();
                    });
                    return true;
                    
                // Add more table creation logic as needed
                default:
                    return false;
            }
        } catch (\Exception $e) {
            $this->error("Failed to create table {$table}: " . $e->getMessage());
            return false;
        }
    }
}
