<?php

namespace App\Containers\AppSection\Member\UI\API\Responders;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Authentication\Data\DTOs\PasswordToken;
use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Values\MemberCsrfToken;
use App\Containers\AppSection\Member\UI\API\Transformers\MemberTokenTransformer;
use App\Containers\AppSection\Member\UI\API\Transformers\MemberTransformer;
use App\Containers\AppSection\Member\Values\MemberClientType;
use App\Containers\AppSection\Member\Values\MemberRefreshToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

final class MemberTokenResponder
{
    public function login(Member $member, PasswordToken $token, string $clientType): JsonResponse
    {
        $meta = MemberTokenTransformer::payload($token, $clientType);
        $response = Response::create($member, MemberTransformer::class)
            ->addMeta($meta)
            ->ok();

        if (MemberClientType::isMobile($clientType)) {
            return $response;
        }

        $cookie = MemberRefreshToken::create($token->refreshToken->value())->asCookie();

        $response = $response->withCookie($cookie);

        if (MemberCsrfToken::enabled()) {
            $csrf = MemberCsrfToken::generate();
            $response = $response
                ->withCookie(MemberCsrfToken::createCookie($csrf));
            $response->headers->set(MemberCsrfToken::headerName(), $csrf);
        }

        return $response;
    }

    public function refresh(PasswordToken $token, string $clientType): JsonResponse
    {
        $cookie = MemberRefreshToken::create($token->refreshToken->value())->asCookie();

        $response = Response::create($token, MemberTokenTransformer::class)->ok();

        if (MemberClientType::isMobile($clientType)) {
            return $response;
        }

        $response = $response->withCookie($cookie);

        if (MemberCsrfToken::enabled()) {
            $csrf = MemberCsrfToken::generate();
            $response = $response
                ->withCookie(MemberCsrfToken::createCookie($csrf));
            $response->headers->set(MemberCsrfToken::headerName(), $csrf);
        }

        return $response;
    }

    public function redirectLogin(Member $member, PasswordToken $token, string $redirectUrl): RedirectResponse
    {
        $meta = MemberTokenTransformer::payload($token, MemberClientType::WEB);
        $payload = $meta;

        $response = redirect()->to($redirectUrl);
        $response = $response->withCookie(
            MemberRefreshToken::create($token->refreshToken->value())->asCookie(),
        );

        if (MemberCsrfToken::enabled()) {
            $csrf = MemberCsrfToken::generate();
            $payload['csrf_token'] = $csrf;
            $response = $response->withCookie(MemberCsrfToken::createCookie($csrf));
        }

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');

        return $response->setTargetUrl($this->buildRedirectUrl($redirectUrl, $payload));
    }

    private function buildRedirectUrl(string $baseUrl, array $payload): string
    {
        $query = http_build_query($payload);

        if (str_contains($baseUrl, '#')) {
            [$base, $fragment] = explode('#', $baseUrl, 2);
            $separator = str_contains($fragment, '?') ? '&' : '?';

            return $base . '#' . $fragment . $separator . $query;
        }

        return $baseUrl . '#' . $query;
    }
}
