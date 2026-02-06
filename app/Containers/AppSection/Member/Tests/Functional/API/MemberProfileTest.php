<?php

namespace App\Containers\AppSection\Member\Tests\Functional\API;

use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Tests\Functional\ApiTestCase;

class MemberProfileTest extends ApiTestCase
{
    protected string $endpoint = '/v1/member/profile';

    public function testGetProfileAuthenticated(): void
    {
        $member = Member::factory()->create()->refresh();

        $response = $this->actingAs($member, 'member')->getJson($this->endpoint);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'type',
                'id',
                'name',
                'email',
            ],
        ]);
        $response->assertJsonPath('data.email', $member->email);
    }

    public function testGetProfileUnauthenticated(): void
    {
        $response = $this->getJson($this->endpoint);

        $response->assertUnauthorized();
    }

    public function testUpdateProfile(): void
    {
        $member = Member::factory()->create([
            'name' => 'Old Name',
        ])->refresh();

        $data = [
            'name' => 'New Name',
            'description' => 'New Description',
        ];

        $response = $this->actingAs($member, 'member')->putJson($this->endpoint, $data);

        $response->assertOk();
        $response->assertJsonPath('data.name', 'New Name');
        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'name' => 'New Name',
            'description' => 'New Description',
        ]);

        $this->assertDatabaseHas('member_activity_logs', [
            'member_id' => $member->id,
            'action' => 'update_setting',
        ]);
    }

    public function testUpdateProfilePasswordRequiresCurrentPassword(): void
    {
        $member = Member::factory()->create([
            'password' => bcrypt('oldpassword'),
        ])->refresh();

        $response = $this->actingAs($member, 'member')->putJson($this->endpoint, [
            'password' => 'Newpassword1!',
            'password_confirmation' => 'Newpassword1!',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['current_password']);
    }

    public function testUpdateProfilePasswordWithCurrentPassword(): void
    {
        $member = Member::factory()->create([
            'password' => bcrypt('oldpassword'),
        ])->refresh();

        $response = $this->actingAs($member, 'member')->putJson($this->endpoint, [
            'current_password' => 'oldpassword',
            'password' => 'Newpassword1!',
            'password_confirmation' => 'Newpassword1!',
        ]);

        $response->assertOk();

        $member->refresh();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('Newpassword1!', $member->password));
    }

    public function testMemberCannotChangeUsernameWhenAlreadySet(): void
    {
        $member = Member::factory()->create([
            'username' => 'first-username',
        ])->refresh();

        $response = $this->actingAs($member, 'member')->putJson($this->endpoint, [
            'username' => 'second-username',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['username']);
    }

    public function testMemberCanSetUsernameWhenMissing(): void
    {
        $member = Member::factory()->create([
            'username' => null,
        ])->refresh();

        $response = $this->actingAs($member, 'member')->putJson($this->endpoint, [
            'username' => 'new-username',
        ]);

        $response->assertOk();
        $member->refresh();
        $this->assertEquals('new-username', $member->username);
        $this->assertNotNull($member->username_changed_at);
    }

    public function testMemberCannotChangeEmailWhenAlreadySet(): void
    {
        $member = Member::factory()->create([
            'email' => 'old-email@test.com',
        ])->refresh();

        $response = $this->actingAs($member, 'member')->putJson($this->endpoint, [
            'email' => 'new-email@test.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }
}
