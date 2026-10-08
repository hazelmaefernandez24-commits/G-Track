<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPasswordLockTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_admin_cannot_edit_another_account_after_its_password_was_changed(): void
    {
        $mainAdmin = $this->createAdmin('main');
        $target = $this->createAdmin('education', [
            'staff_id' => 'EDU001',
            'email' => 'education@example.com',
            'first_name' => 'Education',
            'password_changed_at' => now(),
        ]);

        $this->actingAs($mainAdmin, 'admin')
            ->from('/admin/admins')
            ->put(route('admins.update', $target), [
                'first_name' => 'Changed',
                'middle_initial' => '',
                'last_name' => 'Account',
                'email' => 'education@example.com',
                'role' => 'education',
            ])
            ->assertRedirect('/admin/admins')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('admins', [
            'id' => $target->id,
            'first_name' => 'Education',
            'email' => 'education@example.com',
        ]);
    }

    public function test_password_reset_locks_account_from_edits_by_other_main_admins(): void
    {
        $mainAdmin = $this->createAdmin('main');
        $target = $this->createAdmin('education', [
            'staff_id' => 'EDU001',
            'email' => 'education@example.com',
        ]);

        $this->post(route('reset-password.store'), [
            'staff_id' => 'EDU001',
            'email' => 'education@example.com',
            'password' => 'newpass1',
            'password_confirmation' => 'newpass1',
        ])->assertRedirect(route('login'));

        $this->assertNotNull($target->fresh()->password_changed_at);

        $this->actingAs($mainAdmin, 'admin')
            ->from('/admin/admins')
            ->put(route('admins.update', $target), [
                'first_name' => 'Changed',
                'middle_initial' => '',
                'last_name' => 'Account',
                'email' => 'education@example.com',
                'role' => 'education',
            ])
            ->assertSessionHas('error');
    }

    public function test_main_admin_can_still_delete_an_account_after_its_password_was_changed(): void
    {
        $mainAdmin = $this->createAdmin('main');
        $target = $this->createAdmin('education', [
            'password_changed_at' => now(),
        ]);

        $this->actingAs($mainAdmin, 'admin')
            ->delete(route('admins.destroy', $target))
            ->assertRedirect();

        $this->assertDatabaseMissing('admins', ['id' => $target->id]);
    }

    private function createAdmin(string $role, array $attributes = []): Admin
    {
        $passwordChangedAt = $attributes['password_changed_at'] ?? null;
        unset($attributes['password_changed_at']);

        $admin = Admin::create(array_merge([
            'staff_id' => strtoupper($role).'001',
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'email' => $role.'-'.uniqid().'@example.com',
            'password' => Hash::make('password123'),
            'role' => $role,
        ], $attributes));

        if ($passwordChangedAt) {
            $admin->password_changed_at = $passwordChangedAt;
            $admin->save();
        }

        return $admin;
    }
}
