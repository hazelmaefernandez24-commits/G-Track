<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FirstLoginPasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_sub_admin_must_choose_a_new_password_before_accessing_the_app(): void
    {
        $mainAdmin = $this->createAdmin('main');

        $this->actingAs($mainAdmin, 'admin')
            ->post(route('admins.store'), [
                'first_name' => 'New',
                'middle_initial' => '',
                'last_name' => 'Subadmin',
                'email' => 'subadmin@example.com',
                'password' => 'temporary1',
                'password_confirmation' => 'temporary1',
                'role' => 'education',
            ])
            ->assertRedirect(route('admins.index'));

        $subAdmin = Admin::where('email', 'subadmin@example.com')->firstOrFail();
        $this->assertTrue($subAdmin->must_change_password);

        auth('admin')->logout();

        $this->post(route('login'), [
            'staff_id' => $subAdmin->staff_id,
            'password' => 'temporary1',
        ])->assertRedirect(route('account.first-login-password.show'));

        $this->get(route('dashboard'))
            ->assertRedirect(route('account.first-login-password.show'));
        $this->get(route('account.first-login-password.show'))
            ->assertOk()
            ->assertSee('Choose your password');
    }

    public function test_sub_admin_can_set_a_different_password_and_continue(): void
    {
        $subAdmin = $this->createAdmin('education', ['must_change_password' => true]);
        $this->actingAs($subAdmin, 'admin');

        $this->put(route('account.first-login-password.update'), [
            'new_password' => 'personal1',
            'new_password_confirmation' => 'personal1',
        ])->assertRedirect(route('dashboard'));

        $subAdmin->refresh();
        $this->assertFalse($subAdmin->must_change_password);
        $this->assertTrue(Hash::check('personal1', $subAdmin->password));
        $this->assertNotNull($subAdmin->password_changed_at);
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_temporary_password_cannot_be_reused_as_the_new_password(): void
    {
        $subAdmin = $this->createAdmin('education', ['must_change_password' => true]);
        $this->actingAs($subAdmin, 'admin')
            ->from(route('account.first-login-password.show'))
            ->put(route('account.first-login-password.update'), [
                'new_password' => 'password123',
                'new_password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('account.first-login-password.show'))
            ->assertSessionHasErrors('new_password');

        $this->assertTrue($subAdmin->fresh()->must_change_password);
    }

    public function test_new_main_admin_is_not_required_to_change_password(): void
    {
        $mainAdmin = $this->createAdmin('main', ['must_change_password' => false]);

        $this->actingAs($mainAdmin, 'admin')
            ->get(route('dashboard'))
            ->assertOk();

        $this->put(route('account.first-login-password.update'), [
            'new_password' => 'changed123',
            'new_password_confirmation' => 'changed123',
        ])->assertForbidden();

        $this->assertTrue(Hash::check('password123', $mainAdmin->fresh()->password));
    }

    private function createAdmin(string $role, array $attributes = []): Admin
    {
        return Admin::create(array_merge([
            'staff_id' => strtoupper($role).'001',
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'email' => $role.'-'.uniqid().'@example.com',
            'password' => Hash::make('password123'),
            'role' => $role,
        ], $attributes));
    }
}
