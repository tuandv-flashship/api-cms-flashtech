<?php

namespace App\Containers\AppSection\Member\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Member\Actions\GetSocialLoginUrlAction;
use App\Containers\AppSection\Member\Actions\HandleSocialLoginCallbackAction;
use App\Containers\AppSection\Member\UI\API\Requests\SocialLoginRequest;
use App\Containers\AppSection\Member\UI\API\Responders\MemberTokenResponder;
use App\Containers\AppSection\Member\Values\MemberClientType;
use App\Ship\Parents\Controllers\ApiController;
use App\Ship\Responders\ApiErrorResponder;
use App\Ship\Values\ApiError;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\Access\AuthorizationException;

final class SocialLoginController extends ApiController
{
    public function __construct(
        private readonly GetSocialLoginUrlAction $getSocialLoginUrlAction,
        private readonly HandleSocialLoginCallbackAction $handleSocialLoginCallbackAction,
        private readonly MemberTokenResponder $memberTokenResponder,
        private readonly ApiErrorResponder $apiErrorResponder,
    ) {
    }

    public function redirectToProvider(SocialLoginRequest $request): JsonResponse
    {
        try {
            $clientType = MemberClientType::fromRequest($request);
            $redirectUrl = $this->resolveCallbackRedirectUrl($request->provider, $clientType);

            $url = $this->getSocialLoginUrlAction->run($request->provider, $redirectUrl);

            return Response::json(['url' => $url]);
        } catch (AuthorizationException $exception) {
            return $this->apiErrorResponder->respond(ApiError::create(
                status: 403,
                message: $exception->getMessage(),
                errorCode: 'social_login_disabled',
            ));
        }
    }

    public function handleProviderCallback(SocialLoginRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $clientType = MemberClientType::fromRequest($request);
            $redirectUrl = $this->resolveCallbackRedirectUrl($request->provider, $clientType);

            $result = $this->handleSocialLoginCallbackAction->run(
                $request->provider,
                $redirectUrl,
                $clientType,
            );

            if (!MemberClientType::isMobile($clientType)) {
                $webRedirectUrl = $this->webRedirectUrl();
                if ($webRedirectUrl) {
                    return $this->memberTokenResponder->redirectLogin(
                        $result['member'],
                        $result['token'],
                        $webRedirectUrl,
                    );
                }
            }

            return $this->memberTokenResponder->login(
                $result['member'],
                $result['token'],
                $clientType,
            );
        } catch (AuthorizationException $exception) {
            $code = match ($exception->getMessage()) {
                'Social login provider is disabled.' => 'social_login_disabled',
                'Social login provider id is missing.' => 'social_login_provider_id_missing',
                'Social login email is missing.' => 'social_login_email_missing',
                default => 'social_login_failed',
            };

            return $this->apiErrorResponder->respond(ApiError::create(
                status: 403,
                message: $exception->getMessage(),
                errorCode: $code,
            ));
        }
    }

    private function resolveCallbackRedirectUrl(string $provider, string $clientType): string|null
    {
        if (!MemberClientType::isMobile($clientType)) {
            return null;
        }

        return route('api_member_social_login_callback', [
            'provider' => $provider,
            'client' => MemberClientType::MOBILE,
        ]);
    }

    private function webRedirectUrl(): string|null
    {
        $url = (string) config('member.social.web_redirect_url', '');

        if ($url === '') {
            return null;
        }

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }
}
