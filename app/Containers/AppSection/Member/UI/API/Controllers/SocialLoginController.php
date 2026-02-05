<?php

namespace App\Containers\AppSection\Member\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Member\Actions\GetSocialLoginUrlAction;
use App\Containers\AppSection\Member\Actions\HandleSocialLoginCallbackAction;
use App\Containers\AppSection\Member\UI\API\Requests\SocialLoginRequest;
use App\Containers\AppSection\Member\UI\API\Responders\MemberTokenResponder;
use App\Containers\AppSection\Member\Values\MemberClientType;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Throwable;

class SocialLoginController extends ApiController
{
    public function redirectToProvider(SocialLoginRequest $request): JsonResponse
    {
        try {
            $clientType = MemberClientType::fromRequest($request);
            $redirectUrl = null;

            if (MemberClientType::isMobile($clientType)) {
                $redirectUrl = route('api_member_social_login_callback', [
                    'provider' => $request->provider,
                    'client' => MemberClientType::MOBILE,
                ]);
            }

            $url = app(GetSocialLoginUrlAction::class)->run($request->provider, $redirectUrl);

            return Response::json(['url' => $url]);
        } catch (AuthorizationException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'error_code' => 'social_login_disabled',
            ], 403);
        }
    }

    public function handleProviderCallback(SocialLoginRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $clientType = MemberClientType::fromRequest($request);
            $redirectUrl = null;

            if (MemberClientType::isMobile($clientType)) {
                $redirectUrl = route('api_member_social_login_callback', [
                    'provider' => $request->provider,
                    'client' => MemberClientType::MOBILE,
                ]);
            }

            $result = app(HandleSocialLoginCallbackAction::class)->run(
                $request->provider,
                $redirectUrl,
                $clientType,
            );

            if (!MemberClientType::isMobile($clientType)) {
                $webRedirectUrl = $this->webRedirectUrl();
                if ($webRedirectUrl) {
                    return app(MemberTokenResponder::class)->redirectLogin(
                        $result['member'],
                        $result['token'],
                        $webRedirectUrl,
                    );
                }
            }

            return app(MemberTokenResponder::class)->login(
                $result['member'],
                $result['token'],
                $clientType,
            );
        } catch (AuthorizationException $exception) {
            $code = match ($exception->getMessage()) {
                'Social login provider is disabled.' => 'social_login_disabled',
                'Social login email is missing.' => 'social_login_email_missing',
                default => 'social_login_failed',
            };

            return response()->json([
                'message' => $exception->getMessage(),
                'error_code' => $code,
            ], 403);
        } catch (Throwable $exception) {
            throw $exception;
        }
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
