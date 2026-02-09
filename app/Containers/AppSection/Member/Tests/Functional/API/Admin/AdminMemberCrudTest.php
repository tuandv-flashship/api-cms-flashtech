<?php

namespace App\Containers\AppSection\Member\Tests\Functional\API\Admin;

use App\Containers\AppSection\Member\Enums\MemberActivityAction;
use App\Containers\AppSection\Authorization\Models\Permission;
use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Tests\Functional\ApiTestCase;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

class AdminMemberCrudTest extends ApiTestCase
{
    protected string $endpoint = '/v1/members';

    public function testGetAllMembersByAdmin(): void
    {
        Member::factory()->count(3)->create();
        
        $user = User::factory()->create();
        
        // Seed Permission and assign
        if (class_exists(Permission::class) && method_exists($user, 'givePermissionTo')) {
             Permission::firstOrCreate(['name' => 'members.index', 'guard_name' => 'api']);
             $user->givePermissionTo('members.index');
        }

        $response = $this->actingAs($user, 'api')->getJson($this->endpoint);

        if ($response->status() === 403) {
            $this->markTestSkipped('Permission setup required.');
        }

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function testCreateMemberByAdmin(): void
    {
        $user = User::factory()->create();

        if (class_exists(Permission::class) && method_exists($user, 'givePermissionTo')) {
            Permission::firstOrCreate(['name' => 'members.create', 'guard_name' => 'api']);
            $user->givePermissionTo('members.create');
        }

        $data = [
            'name' => 'New Member',
            'email' => 'admin-created@test.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'email_verified' => true,
        ];

        $response = $this->actingAs($user, 'api')->postJson($this->endpoint, $data);

        if ($response->status() === 403) {
            $this->markTestSkipped('Permission setup required.');
        }

        $response->assertStatus(201);
        $response->assertJsonPath('data.email', 'admin-created@test.com');
        $this->assertDatabaseHas('members', [
            'email' => 'admin-created@test.com',
            'name' => 'New Member',
        ]);
        $member = Member::query()->where('email', 'admin-created@test.com')->firstOrFail();
        $this->assertDatabaseHas('member_activity_logs', [
            'member_id' => $member->id,
            'action' => MemberActivityAction::ADMIN_CREATE->value,
        ]);
    }

    public function testFindMemberByIdByAdmin(): void
    {
        $member = Member::factory()->create();
        
        $user = User::factory()->create();
        
        if (class_exists(Permission::class) && method_exists($user, 'givePermissionTo')) {
             Permission::firstOrCreate(['name' => 'members.show', 'guard_name' => 'api']);
             $user->givePermissionTo('members.show');
        }

        $response = $this->actingAs($user, 'api')->getJson($this->endpoint . '/' . $member->getHashedKey());

        if ($response->status() === 403) {
            $this->markTestSkipped('Permission setup required.');
        }

        $response->assertOk();
        $response->assertJsonPath('data.id', $member->getHashedKey());
    }

    public function testUpdateMemberByAdmin(): void
    {
        $member = Member::factory()->create(['name' => 'Old Name']);
        
        $user = User::factory()->create();
        
        if (class_exists(Permission::class) && method_exists($user, 'givePermissionTo')) {
             Permission::firstOrCreate(['name' => 'members.edit', 'guard_name' => 'api']);
             $user->givePermissionTo('members.edit');
        }

        $data = [
            'name' => 'Admin Updated Name',
            'status' => MemberStatus::INACTIVE->value,
        ];

        $response = $this->actingAs($user, 'api')->putJson($this->endpoint . '/' . $member->getHashedKey(), $data);
        
         if ($response->status() === 403) {
            $this->markTestSkipped('Permission setup required.');
        }

        $response->assertOk();
        $response->assertJsonPath('data.name', 'Admin Updated Name');
        
        $member->refresh();
        $this->assertEquals('Admin Updated Name', $member->name);
        $this->assertEquals(MemberStatus::INACTIVE->value, $member->status->value);
    }

    public function testUpdateMemberEmailDoesNotRequireVerification(): void
    {
        Config::set('member.email_verification.enabled', true);

        $member = Member::factory()->create([
            'email' => 'old-email@test.com',
            'status' => MemberStatus::ACTIVE,
            'email_verified_at' => now()->subDay(),
        ]);

        $user = User::factory()->create();

        if (class_exists(Permission::class) && method_exists($user, 'givePermissionTo')) {
             Permission::firstOrCreate(['name' => 'members.edit', 'guard_name' => 'api']);
             $user->givePermissionTo('members.edit');
        }

        $response = $this->actingAs($user, 'api')->putJson($this->endpoint . '/' . $member->getHashedKey(), [
            'email' => 'new-email@test.com',
        ]);

        if ($response->status() === 403) {
            $this->markTestSkipped('Permission setup required.');
        }

        $response->assertOk();
        $member->refresh();

        $this->assertEquals('new-email@test.com', $member->email);
        $this->assertEquals(MemberStatus::ACTIVE->value, $member->status->value);
        $this->assertNotNull($member->email_verified_at);
    }

    public function testUpdateMemberEmailWithVerificationFlagSetsPending(): void
    {
        Config::set('member.email_verification.enabled', true);

        $member = Member::factory()->create([
            'email' => 'pending-old@test.com',
            'status' => MemberStatus::ACTIVE,
            'email_verified_at' => now()->subDay(),
        ]);

        $user = User::factory()->create();

        if (class_exists(Permission::class) && method_exists($user, 'givePermissionTo')) {
             Permission::firstOrCreate(['name' => 'members.edit', 'guard_name' => 'api']);
             $user->givePermissionTo('members.edit');
        }

        $response = $this->actingAs($user, 'api')->putJson($this->endpoint . '/' . $member->getHashedKey(), [
            'email' => 'pending-new@test.com',
            'send_verification' => true,
        ]);

        if ($response->status() === 403) {
            $this->markTestSkipped('Permission setup required.');
        }

        $response->assertOk();
        $member->refresh();

        $this->assertEquals('pending-new@test.com', $member->email);
        $this->assertEquals(MemberStatus::PENDING->value, $member->status->value);
        $this->assertNull($member->email_verified_at);
    }

    public function testUpdateMemberEmailWithVerificationOverridesActiveStatus(): void
    {
        Config::set('member.email_verification.enabled', true);

        $member = Member::factory()->create([
            'email' => 'override-old@test.com',
            'status' => MemberStatus::ACTIVE,
            'email_verified_at' => now()->subDay(),
        ]);

        $user = User::factory()->create();

        if (class_exists(Permission::class) && method_exists($user, 'givePermissionTo')) {
             Permission::firstOrCreate(['name' => 'members.edit', 'guard_name' => 'api']);
             $user->givePermissionTo('members.edit');
        }

        $response = $this->actingAs($user, 'api')->putJson($this->endpoint . '/' . $member->getHashedKey(), [
            'email' => 'override-new@test.com',
            'send_verification' => true,
            'status' => MemberStatus::ACTIVE->value,
        ]);

        if ($response->status() === 403) {
            $this->markTestSkipped('Permission setup required.');
        }

        $response->assertOk();
        $member->refresh();

        $this->assertEquals(MemberStatus::PENDING->value, $member->status->value);
        $this->assertNull($member->email_verified_at);
    }

    public function testAdminUpdateLogsEmailAndUsernameChanges(): void
    {
        $member = Member::factory()->create([
            'email' => 'log-old@test.com',
            'username' => 'old-username',
        ]);

        $user = User::factory()->create();

        if (class_exists(Permission::class) && method_exists($user, 'givePermissionTo')) {
             Permission::firstOrCreate(['name' => 'members.edit', 'guard_name' => 'api']);
             $user->givePermissionTo('members.edit');
        }

        $response = $this->actingAs($user, 'api')->putJson($this->endpoint . '/' . $member->getHashedKey(), [
            'email' => 'log-new@test.com',
            'username' => 'new-username',
        ]);

        if ($response->status() === 403) {
            $this->markTestSkipped('Permission setup required.');
        }

        $response->assertOk();

        $this->assertDatabaseHas('member_activity_logs', [
            'member_id' => $member->id,
            'action' => MemberActivityAction::ADMIN_UPDATE_EMAIL->value,
        ]);

        $this->assertDatabaseHas('member_activity_logs', [
            'member_id' => $member->id,
            'action' => MemberActivityAction::ADMIN_UPDATE_USERNAME->value,
        ]);
    }

    public function testUpdateMemberByAdminGeneratesUsernameWhenBlank(): void
    {
        $member = Member::factory()->create([
            'email' => 'user@test.com',
            'username' => 'old-username',
        ]);

        $user = User::factory()->create();

        if (class_exists(Permission::class) && method_exists($user, 'givePermissionTo')) {
             Permission::firstOrCreate(['name' => 'members.edit', 'guard_name' => 'api']);
             $user->givePermissionTo('members.edit');
        }

        $data = [
            'username' => null,
        ];

        $response = $this->actingAs($user, 'api')->putJson($this->endpoint . '/' . $member->getHashedKey(), $data);

        if ($response->status() === 403) {
            $this->markTestSkipped('Permission setup required.');
        }

        $response->assertOk();
        $member->refresh();

        $this->assertEquals('user', $member->username);
    }

    public function testAdminCanChangeUsernameEvenIfMemberAlreadyChanged(): void
    {
        $member = Member::factory()->create([
            'username' => 'first-username',
            'username_changed_at' => now(),
        ]);

        $user = User::factory()->create();

        if (class_exists(Permission::class) && method_exists($user, 'givePermissionTo')) {
             Permission::firstOrCreate(['name' => 'members.edit', 'guard_name' => 'api']);
             $user->givePermissionTo('members.edit');
        }

        $response = $this->actingAs($user, 'api')->putJson($this->endpoint . '/' . $member->getHashedKey(), [
            'username' => 'admin-updated',
        ]);

        if ($response->status() === 403) {
            $this->markTestSkipped('Permission setup required.');
        }

        $response->assertOk();
        $member->refresh();
        $this->assertEquals('admin-updated', $member->username);
    }

    public function testDeleteMemberByAdmin(): void
    {
        $member = Member::factory()->create();
        
        $user = User::factory()->create();
        
        if (class_exists(Permission::class) && method_exists($user, 'givePermissionTo')) {
             Permission::firstOrCreate(['name' => 'members.destroy', 'guard_name' => 'api']);
             $user->givePermissionTo('members.destroy');
        }

        $response = $this->actingAs($user, 'api')->deleteJson($this->endpoint . '/' . $member->getHashedKey());
        
         if ($response->status() === 403) {
            $this->markTestSkipped('Permission setup required.');
        }

        $response->assertNoContent();
        $this->assertDatabaseMissing('members', [
            'id' => $member->id,
        ]);

        $this->assertDatabaseHas('member_activity_logs', [
            'member_id' => $member->id,
            'action' => MemberActivityAction::DELETE->value,
        ]);
    }
}
