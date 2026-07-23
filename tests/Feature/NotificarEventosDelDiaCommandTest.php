<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Pueblo;
use App\Models\User;
use App\Notifications\EventosMiPueblo;
use App\Notifications\EventosPrincipalesOtrosPueblos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificarEventosDelDiaCommandTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioSuscrito(?int $puebloId, bool $otrosPueblos, bool $miPueblo): User
    {
        $user = User::factory()->create([
            'pueblo_id' => $puebloId,
            'notif_eventos_otros_pueblos' => $otrosPueblos,
            'notif_eventos_mi_pueblo' => $miPueblo,
        ]);

        $user->pushSubscriptions()->create([
            'endpoint' => 'https://fake.endpoint/'.$user->id,
            'public_key' => 'clave',
            'auth_token' => 'token',
            'content_encoding' => 'aesgcm',
        ]);

        return $user;
    }

    public function test_notifies_correct_events_to_each_user_based_on_preferences(): void
    {
        Notification::fake();

        $puebloA = Pueblo::create(['nombre' => 'Pueblo A', 'slug' => 'pueblo-a']);
        $puebloB = Pueblo::create(['nombre' => 'Pueblo B', 'slug' => 'pueblo-b']);

        $principalA = Evento::create(['pueblo_id' => $puebloA->id, 'titulo' => 'Principal A', 'slug' => 'principal-a', 'fecha_inicio' => now()->setTime(14, 0), 'es_principal' => true]);
        $normalA = Evento::create(['pueblo_id' => $puebloA->id, 'titulo' => 'Normal A', 'slug' => 'normal-a', 'fecha_inicio' => now()->setTime(20, 0), 'es_principal' => false]);
        $principalB = Evento::create(['pueblo_id' => $puebloB->id, 'titulo' => 'Principal B', 'slug' => 'principal-b', 'fecha_inicio' => now()->setTime(18, 0), 'es_principal' => true]);

        $userA = $this->usuarioSuscrito($puebloA->id, otrosPueblos: true, miPueblo: true);
        $userB = $this->usuarioSuscrito($puebloB->id, otrosPueblos: true, miPueblo: false);
        $userSinPueblo = $this->usuarioSuscrito(null, otrosPueblos: true, miPueblo: true);
        $userSinInteres = $this->usuarioSuscrito($puebloA->id, otrosPueblos: false, miPueblo: false);

        $this->artisan('notificaciones:eventos-del-dia')->assertSuccessful();

        Notification::assertSentTo($userA, EventosPrincipalesOtrosPueblos::class, function ($notification) use ($principalB) {
            $eventos = (new \ReflectionProperty($notification, 'eventos'))->getValue($notification);

            return $eventos->pluck('id')->all() === [$principalB->id];
        });

        Notification::assertSentTo($userA, EventosMiPueblo::class, function ($notification) use ($principalA, $normalA) {
            $eventos = (new \ReflectionProperty($notification, 'eventos'))->getValue($notification);

            return $eventos->pluck('id')->sort()->values()->all() === collect([$principalA->id, $normalA->id])->sort()->values()->all();
        });

        Notification::assertSentTo($userB, EventosPrincipalesOtrosPueblos::class, function ($notification) use ($principalA) {
            $eventos = (new \ReflectionProperty($notification, 'eventos'))->getValue($notification);

            return $eventos->pluck('id')->all() === [$principalA->id];
        });
        Notification::assertNotSentTo($userB, EventosMiPueblo::class);

        Notification::assertSentTo($userSinPueblo, EventosPrincipalesOtrosPueblos::class, function ($notification) use ($principalA, $principalB) {
            $eventos = (new \ReflectionProperty($notification, 'eventos'))->getValue($notification);

            return $eventos->pluck('id')->sort()->values()->all() === collect([$principalA->id, $principalB->id])->sort()->values()->all();
        });
        Notification::assertNotSentTo($userSinPueblo, EventosMiPueblo::class);

        Notification::assertNotSentTo($userSinInteres, EventosPrincipalesOtrosPueblos::class);
        Notification::assertNotSentTo($userSinInteres, EventosMiPueblo::class);
    }

    public function test_does_not_notify_when_there_are_no_events_today(): void
    {
        Notification::fake();

        $this->usuarioSuscrito(null, otrosPueblos: true, miPueblo: true);

        $this->artisan('notificaciones:eventos-del-dia')->assertSuccessful();

        Notification::assertNothingSent();
    }
}
