<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Http;

trait VerificaCaptcha
{
    private function verificarCaptcha(string $token): bool
    {
        $secretKey = config('services.recaptcha.secret_key');

        if (! $secretKey) {
            return true;
        }

        $respuesta = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secretKey,
            'response' => $token,
        ]);

        return (bool) $respuesta->json('success');
    }
}
