<?php

namespace App\Containers\AppSection\Member\UI\API\Requests;

use App\Containers\AppSection\Member\Values\MemberRefreshToken;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class RefreshMemberTokenRequest extends ParentRequest
{
    protected array $decode = [];
    
    
    public function rules(): array
    {
        $cookieName = MemberRefreshToken::cookieName();

        return [
            'refresh_token' => [
                'string',
                Rule::requiredIf(
                    fn () => !$this->hasCookie($cookieName),
                ),
            ],
            $cookieName => [
                'string',
                Rule::requiredIf(
                    fn () => !$this->has('refresh_token'),
                ),
            ],
        ];
    }

    public function authorize(): bool
    {
        return (bool) config('member.auth.login_enabled', true)
            && ($this->has('refresh_token') || $this->hasCookie(MemberRefreshToken::cookieName()));
    }

    public function prepareForValidation(): void
    {
        $cookieName = MemberRefreshToken::cookieName();
        if (!is_null($this->cookie($cookieName))) {
            $this->merge([
                $cookieName => $this->cookie($cookieName),
            ]);
        }
    }
}
