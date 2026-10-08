<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangeAdminPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_in_staff_can_open_the_password_change_dialog_from_the_profile_menu(): void
    {
        $staff = $this->createStaff();

        $this->actingAs($staff, 'admin')
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('changePasswordBtn')
            ->assertSee('passwordDialogBackdrop')
            ->assertSee('Change Password')
            ->assertSee('Update Password');
    }

    public function test_staff_can_change_password_with_their_current_password(): void
    {
        $staff = $this->createStaff();

        $this->actingAs($staff, 'admin')
            ->from(route('dashboard'))
            ->put(route('account.password.update'), [
                'current_password' => 'oldpass1',
                'new_password' => 'newpass1',
                'new_password_confirmation' => 'newpass1',
            ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('password_change_status');

        $staff->refresh();
        $this->assertTrue(Hash::check('newpass1', $staff->password));
        $this->assertNotNull($staff->password_changed_at);
    }

    public function test_password_change_requires_correct_current_password_and_minimum_length(): void
    {
        $staff = $this->createStaff();

        $this->actingAs($staff, 'admin')
            ->from(route('dashboard'))
            ->put(route('account.password.update'), [
                'current_password' => 'incorrect',
                'new_password' => 'newpass1',
                'new_password_confirmation' => 'newpass1',
            ])
            ->assertSessionHasErrors('current_password');

        $this->from(route('dashboard'))
            ->put(route('account.password.update'), [
                'current_password' => 'oldpass1',
                'new_password' => 'short',
                'new_password_confirmation' => 'short',
            ])
            ->assertSessionHasErrors('new_password');

        $this->assertTrue(Hash::check('oldpass1', $staff->fresh()->password));
    }

    public function test_main_admin_does_not_see_or_use_self_service_password_change(): void
    {
        $mainAdmin = $this->createStaff('main');

        $this->actingAs($mainAdmin, 'admin')
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('id="changePasswordBtn"')
            ->assertDontSee('id="passwordDialogBackdrop"');

        $this->put(route('account.password.update'), [
            'current_password' => 'oldpass1',
            'new_password' => 'newpass1',
            'new_password_confirmation' => 'newpass1',
        ])->assertForbidden();

        $this->assertTrue(Hash::check('oldpass1', $mainAdmin->fresh()->password));
    }

    private function createStaff(string $role = 'education'): Admin
    {
        return Admin::create([
            'staff_id' => $role === 'main' ? 'MAIN001' : 'EDU001',
            'first_name' => 'Education',
            'last_name' => 'Staff',
            'email' => 'education@example.com',
            'password' => Hash::make('oldpass1'),
            'role' => $role,
        ]);
    }
}
