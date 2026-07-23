<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register');
    }

    public function test_new_users_can_register(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('aceptaTerminos', true);

        $component->call('register');

        $component->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_registration_is_rejected_when_the_honeypot_field_is_filled(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('name', 'Bot User')
            ->set('email', 'bot@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('aceptaTerminos', true)
            ->set('sitioWeb', 'https://spam.example.com');

        $component->call('register');

        $component->assertNoRedirect();

        $this->assertGuest();
        $this->assertNull(User::firstWhere('email', 'bot@example.com'));
    }

    public function test_registration_is_rate_limited_after_too_many_attempts(): void
    {
        RateLimiter::clear('registro|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit('registro|127.0.0.1', 3600);
        }

        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', 'otro@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('aceptaTerminos', true);

        $component->call('register');

        $component->assertHasErrors('name');
        $this->assertGuest();
        $this->assertNull(User::firstWhere('email', 'otro@example.com'));
    }
}
