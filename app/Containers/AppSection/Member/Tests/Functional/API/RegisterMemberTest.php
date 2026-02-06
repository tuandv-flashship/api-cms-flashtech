<?php

namespace App\Containers\AppSection\Member\Tests\Functional\API;

use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Tests\Functional\ApiTestCase;
use Illuminate\Support\Facades\Config;

class RegisterMemberTest extends ApiTestCase
{
    protected string $endpoint = '/v1/member/register';

    public function testRegisterMemberSuccessfully(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+84 123-456-789',
        ];

        $response = $this->postJson($this->endpoint, $data);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'type',
                'id',
                'name',
                'email',
                'status',
                'created_at',
                'updated_at',
            ],
        ]);
        
        $this->assertDatabaseHas('members', [
            'email' => 'john@test.com',
            'name' => 'John Doe',
            'phone' => '+84123456789',
        ]);
    }

    public function testRegisterMemberWithExistingEmail(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Create first member
        $this->postJson($this->endpoint, $data);

        // Try to create second member with same email
        $response = $this->postJson($this->endpoint, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function testRegisterMemberWithEmailVerificationEnabled(): void
    {
        Config::set('member.email_verification.enabled', true);

        $data = [
            'name' => 'John Doe',
            'email' => 'pending@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson($this->endpoint, $data);

        $response->assertStatus(201);
        $this->assertEquals(MemberStatus::PENDING->value, $response->json('data.status'));
    }

    public function testRegisterMemberWithEmailVerificationDisabled(): void
    {
        Config::set('member.email_verification.enabled', false);

        $data = [
            'name' => 'John Doe',
            'email' => 'active@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson($this->endpoint, $data);

        $response->assertStatus(201);
        $this->assertEquals(MemberStatus::ACTIVE->value, $response->json('data.status'));
    }

    public function testRegisterMemberAutoGeneratesUsername(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john2@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson($this->endpoint, $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('members', [
            'email' => 'john2@test.com',
            'username' => 'john2',
        ]);
    }

    public function testRegisterMemberNormalizesPhoneWithLeadingZero(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john3@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '0984572339',
        ];

        $response = $this->postJson($this->endpoint, $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('members', [
            'email' => 'john3@test.com',
            'phone' => '+84984572339',
        ]);
    }

}
