<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use JoelButcher\Socialstream\Features as SocialstreamFeatures;
use JoelButcher\Socialstream\Providers;
use Laravel\Fortify\Features as FortifyFeatures;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SocialstreamRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_socialstream_config_has_social_media_providers(): void
    {
        $providers = config('socialstream.providers');

        $this->assertContains(Providers::github(), $providers);
        $this->assertContains(Providers::google(), $providers);
        $this->assertContains(Providers::facebook(), $providers);
        $this->assertContains(Providers::gitlab(), $providers);
        $this->assertContains(Providers::bitbucket(), $providers);
        $this->assertContains(Providers::linkedin(), $providers);
        $this->assertContains(Providers::linkedinOpenId(), $providers);
        $this->assertContains(Providers::slack(), $providers);
        $this->assertContains(Providers::twitterOAuth2(), $providers);
        $this->assertNotContains(
            Providers::twitterOAuth1(),
            $providers,
            'twitter-oauth-1 must not be enabled (OAuth 1.0 requires live credentials even for redirect)'
        );
    }

    #[DataProvider('socialiteProvidersDataProvider')]
    public function test_users_get_redirected_correctly(string $provider): void
    {
        if (! Providers::enabled($provider)) {
            $this->markTestSkipped("Registration support with the {$provider} provider is not enabled.");
        }

        config()->set("services.{$provider}", [
            'client_id'     => 'client-id',
            'client_secret' => 'client-secret',
            'redirect'      => "http://localhost/oauth/{$provider}/callback",
        ]);

        $response = $this->get("/oauth/{$provider}");
        $response->assertRedirectContains($provider);
    }

    #[DataProvider('socialiteProvidersDataProvider')]
    public function test_users_can_register_using_socialite_providers(string $socialiteProvider): void
    {
        if (! FortifyFeatures::enabled(FortifyFeatures::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        if (! Providers::enabled($socialiteProvider)) {
            $this->markTestSkipped("Registration support with the {$socialiteProvider} provider is not enabled.");
        }

        $user = (new User())
            ->map([
                'id'             => 'abcdefgh',
                'nickname'       => 'Jane',
                'name'           => 'Jane Doe',
                'email'          => 'janedoe@example.com',
                'avatar'         => null,
                'avatar_original' => null,
            ])
            ->setToken('user-token')
            ->setRefreshToken('refresh-token')
            ->setExpiresIn(3600);

        // Enable createAccountOnFirstLogin + globalLogin so canRegister() returns true
        // without requiring a session-forwarded previous_url (which doesn't survive
        // the test kernel's session lifecycle).
        config()->set('socialstream.features', array_merge(
            config('socialstream.features', []),
            [SocialstreamFeatures::createAccountOnFirstLogin(), SocialstreamFeatures::globalLogin()]
        ));

        // Mock via the Socialite contract interface — avoids invalid class names
        // for providers with hyphens (linkedin-openid, twitter-oauth-2, etc.).
        $mockProvider = Mockery::mock(SocialiteProvider::class);
        $mockProvider->shouldReceive('user')->once()->andReturn($user);

        Socialite::shouldReceive('driver')->once()->with($socialiteProvider)->andReturn($mockProvider);

        $response = $this->get("/oauth/{$socialiteProvider}/callback");

        // Verify the user was created via the OAuth provider
        $this->assertDatabaseHas('users', ['email' => 'janedoe@example.com']);
        // Jetstream+Socialstream redirects to Fortify's home after OAuth registration
        $response->assertRedirect(config('fortify.home'));
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function socialiteProvidersDataProvider(): array
    {
        return [
            [Providers::bitbucket()],
            [Providers::facebook()],
            [Providers::github()],
            [Providers::gitlab()],
            [Providers::google()],
            [Providers::linkedin()],
            [Providers::linkedinOpenId()],
            [Providers::slack()],
            [Providers::twitterOAuth2()],
        ];
    }
}
