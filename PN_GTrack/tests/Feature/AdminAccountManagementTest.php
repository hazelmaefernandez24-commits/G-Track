<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_admin_can_edit_an_account_after_its_password_was_changed(): void
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
            ->assertSessionHas('success');

        $this->assertDatabaseHas('admins', [
            'id' => $target->id,
            'first_name' => 'Changed',
            'email' => 'education@example.com',
        ]);
    }

    public function test_main_admin_can_reset_the_password_of_an_account_that_changed_its_password(): void
    {
        $mainAdmin = $this->createAdmin('main');
        $target = $this->createAdmin('education', [
            'staff_id' => 'EDU001',
            'email' => 'education@example.com',
        ]);

        $this->post(route('reset-password.store'), [
            'staff_id' => 'EDU001',
            'email' => 'education@example.com',
            'password' => 'selfpass1',
            'password_confirmation' => 'selfpass1',
        ])->assertRedirect(route('login'));

        $target->refresh();
        $this->assertNotNull($target->password_changed_at);

        $this->actingAs($mainAdmin, 'admin')
            ->from('/admin/admins')
            ->put(route('admins.update', $target), [
                'first_name' => 'Education',
                'middle_initial' => '',
                'last_name' => 'Staff',
                'email' => 'education@example.com',
                'role' => 'education',
                'new_password' => 'adminpass1',
                'new_password_confirmation' => 'adminpass1',
            ])
            ->assertRedirect('/admin/admins')
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('adminpass1', $target->fresh()->password));
    }

    public function test_main_admin_can_still_delete_another_account(): void
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

    public function test_admin_management_page_keeps_edit_control_available_after_password_change(): void
    {
        $mainAdmin = $this->createAdmin('main');
        $this->createAdmin('education', [
            'password_changed_at' => now(),
        ]);

        $this->actingAs($mainAdmin, 'admin')
            ->get(route('admins.index'))
            ->assertOk()
            ->assertSee('Edit')
            ->assertDontSee('Locked after password change');
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
