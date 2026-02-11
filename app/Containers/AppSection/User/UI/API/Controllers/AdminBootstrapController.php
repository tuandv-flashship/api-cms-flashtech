<?php

namespace App\Containers\AppSection\User\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Language\Models\Language;
use App\Ship\Parents\Controllers\ApiController;
use App\Ship\Supports\AdminMenu;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class AdminBootstrapController extends ApiController
{
    public function __construct(
        private readonly AdminMenu $adminMenu,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $user = Auth::user();
        if ($user === null) {
            abort(401);
        }

        $permissions = $user->getAllPermissions()->pluck('name')->values()->all();
        $roles = $user->roles->pluck('name')->values()->all();

        $languages = Language::query()
            ->orderBy('lang_order')
            ->get(['lang_name', 'lang_locale', 'lang_code', 'lang_flag', 'lang_is_default', 'lang_is_rtl']);

        $currentLocale = app()->getLocale();

        return Response::create()->ok([
            'data' => [
                'user' => [
                    'id' => $user->getHashedKey(),
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'roles' => $roles,
                'permissions' => $permissions,
                'admin_menu' => $this->adminMenu->forUser($user),
                'locale' => [
                    'current' => $currentLocale,
                    'available' => $languages->map(fn (Language $lang) => [
                        'name' => $lang->lang_name,
                        'locale' => $lang->lang_locale,
                        'code' => $lang->lang_code,
                        'flag' => $lang->lang_flag,
                        'flag_img' => env('APP_URL') . '/images/flags/' . $lang->lang_flag . '.svg',
                        'is_default' => $lang->lang_is_default,
                        'is_rtl' => $lang->lang_is_rtl,
                    ])->all(),
                ],
            ],
        ]);
    }
}
