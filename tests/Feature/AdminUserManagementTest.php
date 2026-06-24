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
        /** @var User $admin */
        $admin = User::factory()->createOne([
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get('/admin/users');

        $response->assertOk();
    }

    public function test_non_admin_cannot_view_user_management_page(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne([
            'role' => 'user',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/users');

        $response->assertForbidden();
    }

    public function test_admin_can_update_another_users_role(): void
    {
        /** @var User $admin */
        $admin = User::factory()->createOne([
            'role' => 'admin',
        ]);

        /** @var User $targetUser */
        $targetUser = User::factory()->createOne([
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
        /** @var User $admin */
        $admin = User::factory()->createOne([
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

    public function test_admin_can_suspend_and_unsuspend_user(): void
    {
        /** @var User $admin */
        $admin = User::factory()->createOne([
            'role' => 'admin',
        ]);

        /** @var User $targetUser */
        $targetUser = User::factory()->createOne([
            'role' => 'user',
            'suspended_at' => null,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.suspend', $targetUser), [
                'reason' => 'Repeated policy violations.',
            ])
            ->assertRedirect();

        $this->assertNotNull($targetUser->fresh()->suspended_at);

        $this->actingAs($admin)
            ->patch(route('admin.users.unsuspend', $targetUser))
            ->assertRedirect();

        $this->assertNull($targetUser->fresh()->suspended_at);
    }

    public function test_admin_can_delete_user(): void
    {
        /** @var User $admin */
        $admin = User::factory()->createOne([
            'role' => 'admin',
        ]);

        /** @var User $targetUser */
        $targetUser = User::factory()->createOne([
            'role' => 'user',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $targetUser))
            ->assertRedirect();

        $this->assertDatabaseMissing('users', [
            'id' => $targetUser->id,
        ]);
    }

    public function test_admin_can_view_user_activity_history(): void
    {
        /** @var User $admin */
        $admin = User::factory()->createOne([
            'role' => 'admin',
        ]);

        /** @var User $targetUser */
        $targetUser = User::factory()->createOne([
            'role' => 'user',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.users.activity', $targetUser));

        $response->assertOk();
        $response->assertSee('User Activity History');
        $response->assertSee($targetUser->email);
    }

    public function test_non_admin_cannot_manage_user_accounts(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne([
            'role' => 'user',
        ]);

        /** @var User $targetUser */
        $targetUser = User::factory()->createOne([
            'role' => 'user',
        ]);

        $this->actingAs($user)
            ->patch(route('admin.users.suspend', $targetUser), [
                'reason' => 'Unauthorized attempt',
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('admin.users.destroy', $targetUser))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.users.activity', $targetUser))
            ->assertForbidden();
    }
}
