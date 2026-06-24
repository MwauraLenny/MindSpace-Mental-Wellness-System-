<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_user_management_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get('/admin/users');

        $response->assertOk();
    }

    public function test_non_admin_cannot_view_user_management_page(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/users');

        $response->assertForbidden();
    }

    public function test_admin_can_update_another_users_role(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $targetUser = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this
            ->actingAs($admin)
            ->patch(route('admin.users.role.update', $targetUser), [
                'role' => 'admin',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertSame('admin', $targetUser->fresh()->role);
    }

    public function test_admin_cannot_update_own_role(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($admin)
            ->patch(route('admin.users.role.update', $admin), [
                'role' => 'user',
            ]);

        $response->assertRedirect();
        $this->assertSame('admin', $admin->fresh()->role);
    }
}
