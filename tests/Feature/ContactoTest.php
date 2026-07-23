<?php

namespace Tests\Feature;

use App\Mail\ContactoEnviado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ContactoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('contacto|127.0.0.1');
    }

    public function test_the_contact_form_sends_an_email(): void
    {
        Mail::fake();

        Volt::test('public.contacto')
            ->set('nombre', 'María')
            ->set('email', 'maria@example.com')
            ->set('asunto', 'Consulta')
            ->set('descripcion', 'Hola, tengo una pregunta.')
            ->call('enviar')
            ->assertHasNoErrors()
            ->assertSet('enviado', true);

        Mail::assertSent(ContactoEnviado::class);
    }

    public function test_the_contact_form_is_rate_limited_after_too_many_attempts(): void
    {
        Mail::fake();

        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit('contacto|127.0.0.1', 3600);
        }

        Volt::test('public.contacto')
            ->set('nombre', 'María')
            ->set('email', 'maria@example.com')
            ->set('asunto', 'Consulta')
            ->set('descripcion', 'Hola, tengo una pregunta.')
            ->call('enviar')
            ->assertHasErrors('descripcion');

        Mail::assertNothingSent();
    }
}
