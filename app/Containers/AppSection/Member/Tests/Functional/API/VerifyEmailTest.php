<?php

namespace App\Containers\AppSection\Member\Tests\Functional\API;

use App\Containers\AppSection\Member\Enums\MemberActivityAction;
use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Tests\Functional\ApiTestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailTest extends ApiTestCase
{
    public function testVerifyEmailSuccessfully(): void
    {
        Config::set('member.email_verification.enabled', true);

        $member = Member::factory()->create([
            'status' => MemberStatus::PENDING,
            'email_verified_at' => null,
        ]);

        $url = URL::temporarySignedRoute(
            'api_member_verify_email',
            now()->addMinutes(60),
            [
                'id' => $member->getHashedKey(),
                'hash' => sha1($member->getEmailForVerification()),
            ]
        );

        $response = $this->getJson($url);

        $response->assertOk();

        $member->refresh();
        $this->assertNotNull($member->email_verified_at);
        $this->assertEquals(MemberStatus::ACTIVE->value, $member->status->value);
        $this->assertDatabaseHas('member_activity_logs', [
            'member_id' => $member->id,
            'action' => MemberActivityAction::VERIFY_EMAIL->value,
        ]);
    }

    public function testVerifyEmailDisabledReturnsForbidden(): void
    {
        Config::set('member.email_verification.enabled', false);

        $member = Member::factory()->create([
            'status' => MemberStatus::PENDING,
            'email_verified_at' => null,
        ]);

        $url = URL::temporarySignedRoute(
            'api_member_verify_email',
            now()->addMinutes(60),
            [
                'id' => $member->getHashedKey(),
                'hash' => sha1($member->getEmailForVerification()),
            ]
        );

        $response = $this->getJson($url);

        $response->assertStatus(403);
    }
}
