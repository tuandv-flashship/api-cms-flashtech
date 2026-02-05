<?php

namespace App\Containers\AppSection\Member\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

class SocialLoginRequest extends ParentRequest
{
    protected array $access = [
        'permissions' => '',
        'roles' => '',
    ];

    protected array $decode = [
        //
    ];

    protected array $urlParameters = [
        'provider',
    ];

    protected function prepareForValidation(): void
    {
        $this->merge([
            'provider' => $this->route('provider'),
        ]);
    }

    public function rules(): array
    {
        $enabledProviders = $this->enabledProviders();

        return [
            'provider' => [
                'required',
                Rule::in($enabledProviders),
            ],
        ];
    }

    public function authorize(): bool
    {
        return (bool) config('member.auth.login_enabled', true)
            && !empty($this->enabledProviders());
    }

    /**
     * @return array<int, string>
     */
    private function enabledProviders(): array
    {
        $providers = (array) config('member.social', []);
        $enabled = [];

        foreach ($providers as $provider => $config) {
            if (!empty($config['enabled'])) {
                $enabled[] = $provider;
            }
        }

        return $enabled;
    }
}
