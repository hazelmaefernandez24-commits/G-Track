<?php

namespace App\Services;

use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoggingService
{
    /**
     * Log a user input or system change
     *
     * @param string $actionType The type of action (e.g., 'menu_update', 'user_input')
     * @param string $module The module where the action occurred (e.g., 'menu', 'inventory')
     * @param string $description Description of the action
     * @param array|null $oldValues Old values before the change (optional)
     * @param array|null $newValues New values after the change (optional)
     * @param string|null $userInput Raw user input (optional)
     * @param Request|null $request The request object (optional)
     * @return SystemLog The created log entry
     */
    public static function log(
        string $actionType,
        string $module,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $userInput = null,
        ?Request $request = null
    ): SystemLog {
        $userId = Auth::id();
        $ipAddress = $request ? $request->ip() : null;
        
        return SystemLog::create([
            'user_id' => $userId,
            'action_type' => $actionType,
            'module' => $module,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'user_input' => $userInput,
            'ip_address' => $ipAddress
        ]);
    }
    
    /**
     * Log a menu update
     *
     * @param string $description Description of the menu update
     * @param array|null $oldValues Old menu values
     * @param array|null $newValues New menu values
     * @param string|null $userInput Raw user input
     * @param Request|null $request The request object
     * @return SystemLog The created log entry
     */
    public static function logMenuUpdate(
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $userInput = null,
        ?Request $request = null
    ): SystemLog {
        return self::log(
            'menu_update',
            'menu',
            $description,
            $oldValues,
            $newValues,
            $userInput,
            $request
        );
    }
    
    /**
     * Log a user input
     *
     * @param string $module The module where the input occurred
     * @param string $description Description of the input
     * @param string $userInput Raw user input
     * @param Request|null $request The request object
     * @return SystemLog The created log entry
     */
    public static function logUserInput(
        string $module,
        string $description,
        string $userInput,
        ?Request $request = null
    ): SystemLog {
        return self::log(
            'user_input',
            $module,
            $description,
            null,
            null,
            $userInput,
            $request
        );
    }
}
