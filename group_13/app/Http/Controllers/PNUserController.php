<?php

namespace App\Http\Controllers;

use App\Models\PNUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\TempPasswordMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class PNUserController extends Controller
{
    // Display the list of users
    public function index(Request $request)
    {
        $roleFilter = $request->input('role');
        $statusFilter = $request->input('status');

        $users = PNUser::when($roleFilter, function ($query, $roleFilter) {
            return $query->where('user_role', $roleFilter);
        })->when($statusFilter, function ($query, $statusFilter) {
            return $query->where('status', $statusFilter);
        })->paginate(8);

        // Get all distinct roles
        $roles = PNUser::select('user_role')->distinct()->pluck('user_role');

        return view('admin.pnph_users.index', compact('users', 'roles', 'roleFilter', 'statusFilter'), ['title'=> 'Manage Users']);
    }

    // Show the form for creating a new user
    public function create()
    {
        return view('admin.pnph_users.create', ['title'=> 'Create User']);
    }




    // Store a newly created user in the database
    public function store(Request $request)
    {
        try {
            // Enhanced validation with custom messages
            $request->validate([
                'user_id' => 'required|string|max:20|unique:pnph_users,user_id',
                'user_lname' => 'required|string|max:50',
                'user_fname' => 'required|string|max:50',
                'user_mInitial' => 'nullable|string|max:5',
                'user_suffix' => 'nullable|string|max:10',
                'gender' => 'nullable|in:M,F',
                'user_email' => 'required|email|max:100|unique:pnph_users,user_email',
                'user_role' => 'required|string|in:admin,training,educator,student',
            ], [
                'user_id.required' => 'User ID is required.',
                'user_id.unique' => 'This User ID already exists. Please choose a different one.',
                'user_lname.required' => 'Last name is required.',
                'user_fname.required' => 'First name is required.',
                'gender.in' => 'Gender must be M or F.',
                'user_email.required' => 'Email address is required.',
                'user_email.email' => 'Please enter a valid email address.',
                'user_email.unique' => 'This email address is already registered. Please use a different email.',
                'user_role.required' => 'User role is required.',
                'user_role.in' => 'Please select a valid user role (Admin, Training, Educator, or Student).',
            ]);

            // Start database transaction
            DB::beginTransaction();

            // Generate a secure temporary password
            $password = Str::random(12); // Generate a 12-character random password

            // Create the user in the database
            $user = PNUser::create([
                'user_id' => trim($request->user_id),
                'user_lname' => trim($request->user_lname),
                'user_fname' => trim($request->user_fname),
                'user_mInitial' => $request->user_mInitial ? trim($request->user_mInitial) : null,
                'user_suffix' => $request->user_suffix ? trim($request->user_suffix) : null,
                'gender' => $request->gender ?: null,
                'user_email' => trim(strtolower($request->user_email)),
                'user_role' => $request->user_role,
                'user_password' => Hash::make($password),
                'status' => 'active',
                'is_temp_password' => true,
            ]);

            // Sync user to Login system database
            $this->syncUserToLoginSystem($user, $password);

            // Send email with temporary password
            try {
                Mail::to($user->user_email)->send(new TempPasswordMail($user, $password));
                $emailSent = true;
            } catch (\Exception $e) {
                \Log::error('Failed to send email to new user: ' . $e->getMessage());
                $emailSent = false;
            }

            // Commit the transaction
            DB::commit();

            // Success message with email status
            $message = "User '{$user->user_fname} {$user->user_lname}' has been created successfully.";
            if ($emailSent) {
                $message .= " A temporary password has been sent to {$user->user_email}.";
            } else {
                $message .= " However, the email with temporary password could not be sent. Please contact the user manually.";
            }

            return redirect()->route('admin.pnph_users.index')->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors will be automatically handled by Laravel
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            \Log::error('Error creating user: ' . $e->getMessage(), [
                'user_data' => $request->except(['_token']),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An error occurred while creating the user. Please try again. If the problem persists, contact the system administrator.')
                        ->withInput();
        }
    }

    /**
     * Sync user to Login system database
     */
    private function syncUserToLoginSystem($user, $password)
    {
        try {
            // Get Login system database configuration
            $loginDbConfig = [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => 'pn_systems', // Login system database
                'username' => env('DB_USERNAME', 'forge'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ];

            // Create connection to Login system database
            $loginDb = \DB::connection()->getPdo();
            $loginDb = new \PDO(
                "mysql:host={$loginDbConfig['host']};port={$loginDbConfig['port']};dbname={$loginDbConfig['database']};charset={$loginDbConfig['charset']}",
                $loginDbConfig['username'],
                $loginDbConfig['password']
            );

            // Check if user already exists in Login system
            $stmt = $loginDb->prepare("SELECT user_id FROM pnph_users WHERE user_id = ?");
            $stmt->execute([$user->user_id]);

            if (!$stmt->fetch()) {
                // User doesn't exist, create them
                $insertStmt = $loginDb->prepare("
                    INSERT INTO pnph_users (
                        user_id, user_lname, user_fname, user_mInitial, user_suffix, gender,
                        user_email, user_role, user_password, status, is_temp_password,
                        created_at, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");

                $insertStmt->execute([
                    $user->user_id,
                    $user->user_lname,
                    $user->user_fname,
                    $user->user_mInitial,
                    $user->user_suffix,
                    $user->gender,
                    $user->user_email,
                    $user->user_role,
                    $user->user_password, // Already hashed
                    $user->status,
                    $user->is_temp_password ? 1 : 0
                ]);

                \Log::info("User {$user->user_id} synced to Login system successfully");
            }
        } catch (\Exception $e) {
            \Log::error("Failed to sync user {$user->user_id} to Login system: " . $e->getMessage());
            // Don't throw exception to avoid breaking user creation in group_13
        }
    }




    // Show the form for editing an existing user
    public function edit($user_id)
    {
        $user = PNUser::find($user_id);
        return view('admin.pnph_users.edit', compact('user'), ['title'=> 'Edit User']);
    }






    // Update the user in the database
    public function update(Request $request, $user_id)
    {
        try {
            // Find the user first
            $user = PNUser::findOrFail($user_id);

            // Enhanced validation with custom messages
            $request->validate([
                'user_fname' => 'required|string|max:50',
                'user_lname' => 'required|string|max:50',
                'user_mInitial' => 'nullable|string|max:5',
                'user_suffix' => 'nullable|string|max:10',
                'gender' => 'nullable|in:M,F',
                'user_email' => 'required|email|max:100|unique:pnph_users,user_email,' . $user_id . ',user_id',
                'user_role' => 'required|string|in:Admin,Training,Educator,Student',
                'status' => 'required|in:active,inactive',
            ], [
                'user_fname.required' => 'First name is required.',
                'user_lname.required' => 'Last name is required.',
                'gender.in' => 'Gender must be M or F.',
                'user_email.required' => 'Email address is required.',
                'user_email.email' => 'Please enter a valid email address.',
                'user_email.unique' => 'This email address is already registered to another user.',
                'user_role.required' => 'User role is required.',
                'user_role.in' => 'Please select a valid user role (Admin, Training, Educator, or Student).',
                'status.required' => 'User status is required.',
                'status.in' => 'Please select a valid status (active or inactive).',
            ]);

            // Start database transaction
            DB::beginTransaction();

            // Store original data for comparison
            $originalData = $user->toArray();

            // Update the user
            $user->update([
                'user_fname' => trim($request->user_fname),
                'user_lname' => trim($request->user_lname),
                'user_mInitial' => $request->user_mInitial ? trim($request->user_mInitial) : null,
                'user_suffix' => $request->user_suffix ? trim($request->user_suffix) : null,
                'gender' => $request->gender ?: null,
                'user_email' => trim(strtolower($request->user_email)),
                'user_role' => $request->user_role,
                'status' => $request->status,
            ]);

            // Commit the transaction
            DB::commit();

            // Check what was changed for detailed success message
            $changes = [];
            if ($originalData['user_fname'] !== $user->user_fname || $originalData['user_lname'] !== $user->user_lname) {
                $changes[] = 'name';
            }
            if ($originalData['user_email'] !== $user->user_email) {
                $changes[] = 'email';
            }
            if ($originalData['user_role'] !== $user->user_role) {
                $changes[] = 'role';
            }
            if ($originalData['status'] !== $user->status) {
                $changes[] = 'status';
            }

            $message = "User '{$user->user_fname} {$user->user_lname}' has been updated successfully.";
            if (!empty($changes)) {
                $message .= " Updated: " . implode(', ', $changes) . ".";
            }

            return redirect()->route('admin.pnph_users.index')->with('success', $message);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.pnph_users.index')
                           ->with('error', 'User not found. The user may have been deleted by another administrator.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors will be automatically handled by Laravel
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            \Log::error('Error updating user: ' . $e->getMessage(), [
                'user_id' => $user_id,
                'user_data' => $request->except(['_token', '_method']),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An error occurred while updating the user. Please try again. If the problem persists, contact the system administrator.')
                        ->withInput();
        }
    }



    public function show($user_id)
{
    // Find the user by user_id
    $user = PNUser::findOrFail($user_id);

    // Return a view to display user details
    return view('admin.pnph_users.show', compact('user'), ['title'=> 'View User']);
}


    // Soft delete user (deactivate)
    public function destroy($user_id)
    {
        try {
            // Find the user
            $user = PNUser::findOrFail($user_id);

            // Prevent deletion of the current admin user
            if (auth()->user()->user_id === $user_id) {
                return back()->with('error', 'You cannot delete your own account while logged in.');
            }

            // Start database transaction
            DB::beginTransaction();

            // Soft delete by setting status to inactive
            $user->update(['status' => 'inactive']);

            // Commit the transaction
            DB::commit();

            return redirect()->route('admin.pnph_users.index')
                           ->with('success', "User '{$user->user_fname} {$user->user_lname}' has been deactivated successfully.");

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.pnph_users.index')
                           ->with('error', 'User not found. The user may have already been deleted.');

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            \Log::error('Error deactivating user: ' . $e->getMessage(), [
                'user_id' => $user_id,
                'stack_trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An error occurred while deactivating the user. Please try again.');
        }
    }

    // Permanently delete user (hard delete)
    public function forceDelete($user_id)
    {
        try {
            // Find the user
            $user = PNUser::findOrFail($user_id);

            // Prevent deletion of the current admin user
            if (auth()->user()->user_id === $user_id) {
                return back()->with('error', 'You cannot delete your own account while logged in.');
            }

            // Additional safety check - only allow deletion of inactive users
            if ($user->status === 'active') {
                return back()->with('error', 'Cannot permanently delete an active user. Please deactivate the user first.');
            }

            // Start database transaction
            DB::beginTransaction();

            $userName = $user->user_fname . ' ' . $user->user_lname;

            // Permanently delete the user
            $user->delete();

            // Commit the transaction
            DB::commit();

            return redirect()->route('admin.pnph_users.index')
                           ->with('success', "User '{$userName}' has been permanently deleted.");

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.pnph_users.index')
                           ->with('error', 'User not found. The user may have already been deleted.');

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            \Log::error('Error permanently deleting user: ' . $e->getMessage(), [
                'user_id' => $user_id,
                'stack_trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An error occurred while deleting the user. Please try again.');
        }
    }

    public function dashboard()
    {
        // Get the count of users for each role
        $rolesCount = \App\Models\PNUser::select('user_role', \DB::raw('count(*) as total'))
                                        ->groupBy('user_role')
                                        ->pluck('total', 'user_role')
                                        ->toArray();

        return view('admin.dashboard', compact('rolesCount'), ['title'=> 'Dashboard']);
    }



}
