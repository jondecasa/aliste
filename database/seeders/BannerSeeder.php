<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banner = Banner::obtener();

        if ($banner->contenido_html) {
            return;
        }

        $banner->update([
            'contenido_html' => <<<'HTML'
                <div style="background: oklch(55% 0.14 130); border-radius: 20px; padding: 32px 28px; margin: 8px 0 40px; color: white; display: flex; flex-direction: column; gap: 12px;">
                    <h2 style="font-family: 'Newsreader', Georgia, serif; font-size: 24px; font-weight: 600; margin: 0;">¿Eres representante de tu pueblo?</h2>
                    <p style="margin: 0; font-size: 15px; line-height: 1.6; opacity: 0.95; max-width: 640px;">
                        Si eres alcalde, alcaldesa o representante de alguno de los pueblos de la comarca, regístrate en Aliste.info para mantener actualizada la información de tu localidad: noticias, eventos, puntos de interés y mucho más.
                    </p>
                    <div>
                        <a href="/registro" style="display: inline-block; background: white; color: oklch(55% 0.14 130); padding: 10px 24px; border-radius: 999px; font-weight: 700; font-size: 14px; text-decoration: none; margin-top: 8px;">
                            Regístrate aquí
                        </a>
                    </div>
                </div>
                HTML,
        ]);
    }
}
