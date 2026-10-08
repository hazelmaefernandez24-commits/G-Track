<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EditAdminProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_in_admin_can_open_the_edit_profile_dialog(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('editProfileBtn')
            ->assertSee('profileDialogBackdrop')
            ->assertSee('Edit Profile')
            ->assertSee('First Name')
            ->assertSee('Middle Initial')
            ->assertSee('Last Name')
            ->assertSee('Email')
            ->assertSee('Submit');
    }

    public function test_signed_in_admin_can_update_their_own_profile(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->from(route('dashboard'))
            ->put(route('account.profile.update'), [
                'first_name' => 'Updated',
                'middle_initial' => 'M',
                'last_name' => 'Staff',
                'email' => 'updated@example.com',
            ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('profile_update_status');

        $admin->refresh();
        $this->assertSame('Updated', $admin->first_name);
        $this->assertSame('M', $admin->middle_initial);
        $this->assertSame('Staff', $admin->last_name);
        $this->assertSame('updated@example.com', $admin->email);
        $this->assertSame('education', $admin->role);
        $this->assertTrue(Hash::check('oldpass1', $admin->password));
    }

    public function test_middle_initial_is_optional_and_invalid_profile_data_is_rejected(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->from(route('dashboard'))
            ->put(route('account.profile.update'), [
                'first_name' => 'Updated',
                'middle_initial' => '',
                'last_name' => 'Staff',
                'email' => 'staff@example.com',
            ])
            ->assertSessionHasNoErrors();

        $this->from(route('dashboard'))
            ->put(route('account.profile.update'), [
                'first_name' => 'Updated',
                'middle_initial' => '1',
                'last_name' => 'Staff',
                'email' => 'not-an-email',
            ])
            ->assertSessionHasErrorsIn('profile', ['middle_initial', 'email']);
    }

    public function test_main_admin_does_not_see_or_use_self_edit_profile(): void
    {
        $mainAdmin = $this->createAdmin('main');

        $this->actingAs($mainAdmin, 'admin')
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('id="editProfileBtn"')
            ->assertDontSee('id="profileDialogBackdrop"')
            ->assertSee('changePasswordBtn');

        $this->put(route('account.profile.update'), [
            'first_name' => 'Changed',
            'middle_initial' => '',
            'last_name' => 'Admin',
            'email' => 'main-updated@example.com',
        ])->assertForbidden();

        $this->assertSame('Education', $mainAdmin->fresh()->first_name);
        $this->assertSame('staff@example.com', $mainAdmin->fresh()->email);
    }

    private function createAdmin(string $role = 'education'): Admin
    {
        return Admin::create([
            'staff_id' => $role === 'main' ? 'MAIN001' : 'EDU001',
            'first_name' => 'Education',
            'middle_initial' => null,
            'last_name' => 'Staff',
            'email' => 'staff@example.com',
            'password' => Hash::make('oldpass1'),
            'role' => $role,
        ]);
    }
}
