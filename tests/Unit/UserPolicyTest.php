<?php

namespace Tests\Unit;

use App\Models\User;
use App\Policies\UserPolicy;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    private UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new UserPolicy();
    }

    #[Test]
    public function user_can_manage_own_profile(): void
    {
        $user = User::factory()->make(['id' => 10, 'role' => 'user']);

        $this->assertTrue($this->policy->view($user, $user));
        $this->assertTrue($this->policy->update($user, $user));
        $this->assertTrue($this->policy->delete($user, $user));
    }

    #[Test]
    public function user_cannot_manage_another_users_profile(): void
    {
        $user = User::factory()->make(['id' => 10, 'role' => 'user']);
        $anotherUser = User::factory()->make(['id' => 11, 'role' => 'user']);

        $this->assertFalse($this->policy->view($user, $anotherUser));
        $this->assertFalse($this->policy->update($user, $anotherUser));
        $this->assertFalse($this->policy->delete($user, $anotherUser));
    }

    #[Test]
    public function admin_can_manage_another_users_profile(): void
    {
        $admin = User::factory()->make(['id' => 1, 'role' => 'admin']);
        $user = User::factory()->make(['id' => 2, 'role' => 'user']);

        $this->assertTrue($this->policy->view($admin, $user));
        $this->assertTrue($this->policy->update($admin, $user));
        $this->assertTrue($this->policy->delete($admin, $user));
    }
}
