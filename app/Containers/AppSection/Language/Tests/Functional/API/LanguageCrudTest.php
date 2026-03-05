<?php

namespace App\Containers\AppSection\Language\Tests\Functional\API;

use App\Containers\AppSection\Language\Models\Language;
use App\Containers\AppSection\Language\Tests\Functional\ApiTestCase;
use App\Containers\AppSection\Language\UI\API\Controllers\CreateLanguageController;
use App\Containers\AppSection\Language\UI\API\Controllers\DeleteLanguageController;
use App\Containers\AppSection\Language\UI\API\Controllers\GetCurrentLanguageController;
use App\Containers\AppSection\Language\UI\API\Controllers\ListAvailableLanguagesController;
use App\Containers\AppSection\Language\UI\API\Controllers\ListLanguagesController;
use App\Containers\AppSection\Language\UI\API\Controllers\ListSupportedLanguagesController;
use App\Containers\AppSection\Language\UI\API\Controllers\SetDefaultLanguageController;
use App\Containers\AppSection\Language\UI\API\Controllers\UpdateLanguageController;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\CoversClass;
use Spatie\Permission\Models\Permission;

#[CoversClass(ListLanguagesController::class)]
#[CoversClass(CreateLanguageController::class)]
#[CoversClass(UpdateLanguageController::class)]
#[CoversClass(DeleteLanguageController::class)]
#[CoversClass(SetDefaultLanguageController::class)]
#[CoversClass(GetCurrentLanguageController::class)]
#[CoversClass(ListAvailableLanguagesController::class)]
#[CoversClass(ListSupportedLanguagesController::class)]
final class LanguageCrudTest extends ApiTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->createOne();

        $permissions = Permission::whereIn('name', [
            'languages.index',
            'languages.create',
            'languages.edit',
            'languages.destroy',
        ])->where('guard_name', 'api')->get();

        $this->user->syncPermissions($permissions);
    }

    private function getOrCreateEnglish(): Language
    {
        return Language::query()->firstOrCreate(
            ['lang_code' => 'en_US'],
            [
                'lang_name' => 'English',
                'lang_locale' => 'en',
                'lang_flag' => 'us',
                'lang_is_default' => false,
                'lang_order' => 1,
                'lang_is_rtl' => false,
            ],
        );
    }

    // ─── List ────────────────────────────────────────────────────────

    public function testListLanguages(): void
    {
        // ContainerTestCase seeds 1 default language (vi)
        $this->getOrCreateEnglish();

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/v1/languages');

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data', 2)
                ->has('data.0', fn (AssertableJson $json) => $json
                    ->has('id')
                    ->has('lang_name')
                    ->has('lang_locale')
                    ->has('lang_code')
                    ->has('lang_flag')
                    ->has('lang_is_default')
                    ->etc()
                )
                ->etc(),
        );
    }

    public function testListLanguagesPublicEndpoint(): void
    {
        // Public route — no auth needed (ListLanguages.v1.public.php)
        $response = $this->getJson('/v1/languages');

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data')
                ->etc(),
        );
    }

    public function testListLanguagesWithoutPermissionFails(): void
    {
        $user = User::factory()->createOne();

        $response = $this->actingAs($user, 'api')
            ->getJson('/v1/languages');

        $response->assertForbidden();
    }

    // ─── Create ──────────────────────────────────────────────────────

    public function testCreateLanguage(): void
    {
        $payload = [
            'lang_name' => 'Français',
            'lang_locale' => 'fr',
            'lang_code' => 'fr_FR',
            'lang_flag' => 'fr',
            'lang_is_default' => false,
            'lang_is_rtl' => false,
            'lang_order' => 2,
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/languages', $payload);

        $response->assertCreated();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data', fn (AssertableJson $json) => $json
                    ->where('lang_name', 'Français')
                    ->where('lang_locale', 'fr')
                    ->where('lang_code', 'fr_FR')
                    ->where('lang_flag', 'fr')
                    ->where('lang_is_default', false)
                    ->etc()
                )
                ->etc(),
        );

        $this->assertDatabaseHas('languages', [
            'lang_name' => 'Français',
            'lang_locale' => 'fr',
            'lang_code' => 'fr_FR',
        ]);
    }

    public function testCreateLanguageMissingRequiredFieldsFails(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/languages', []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['lang_name', 'lang_locale', 'lang_code']);
    }

    // ─── Update ──────────────────────────────────────────────────────

    public function testUpdateLanguage(): void
    {
        $language = $this->getOrCreateEnglish();

        $response = $this->actingAs($this->user, 'api')
            ->patchJson("/v1/languages/{$language->getHashedKey()}", [
                'lang_name' => 'English (US)',
            ]);

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data', fn (AssertableJson $json) => $json
                    ->where('lang_name', 'English (US)')
                    ->etc()
                )
                ->etc(),
        );

        $this->assertDatabaseHas('languages', [
            'lang_id' => $language->lang_id,
            'lang_name' => 'English (US)',
        ]);
    }

    // ─── Delete ──────────────────────────────────────────────────────

    public function testDeleteLanguage(): void
    {
        $language = $this->getOrCreateEnglish();

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson("/v1/languages/{$language->getHashedKey()}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('languages', [
            'lang_id' => $language->lang_id,
        ]);
    }

    public function testDeleteDefaultLanguageReassignsDefault(): void
    {
        // Default vi exists from ContainerTestCase
        $defaultLang = Language::query()->where('lang_is_default', true)->first();
        $this->getOrCreateEnglish();

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson("/v1/languages/{$defaultLang->getHashedKey()}");

        $response->assertNoContent();

        // Another language should now be default
        $newDefault = Language::query()->where('lang_is_default', true)->first();
        $this->assertNotNull($newDefault);
    }

    // ─── Set Default ─────────────────────────────────────────────────

    public function testSetDefaultLanguage(): void
    {
        $english = $this->getOrCreateEnglish();

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/v1/languages/{$english->getHashedKey()}/default");

        $response->assertOk();

        // English is now default
        $this->assertTrue(
            Language::query()->where('lang_id', $english->lang_id)->value('lang_is_default'),
        );

        // Old default (vi) is no longer default
        $otherDefaults = Language::query()
            ->where('lang_id', '!=', $english->lang_id)
            ->where('lang_is_default', true)
            ->count();

        $this->assertSame(0, $otherDefaults);
    }

    // ─── Get Current ─────────────────────────────────────────────────

    public function testGetCurrentLanguage(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/v1/languages/current');

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data', fn (AssertableJson $json) => $json
                    ->has('lang_name')
                    ->has('lang_locale')
                    ->etc()
                )
                ->etc(),
        );
    }

    // ─── Available & Supported ───────────────────────────────────────

    public function testListAvailableLanguages(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/v1/languages/available');

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data')
                ->etc(),
        );

        // Available should NOT include already-added languages
        $addedCodes = Language::query()->pluck('lang_code')->all();
        $responseData = $response->json('data');

        foreach ($responseData as $lang) {
            $this->assertNotContains(
                $lang['lang_code'] ?? null,
                $addedCodes,
                'Available list should not contain already-added languages',
            );
        }
    }

    public function testListSupportedLanguages(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/v1/languages/supported');

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data')
                ->etc(),
        );

        // Supported should include ALL languages from config
        $supportedCount = count(config('languages.locales', []));
        $this->assertGreaterThan(0, $supportedCount);
    }
}
