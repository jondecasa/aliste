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
                <h2 style="font-family: 'Newsreader', Georgia, serif; font-size: 24px; font-weight: 600; margin: 0;">&iquest;Eres representante de tu pueblo?</h2>
                <p style="margin: 0; font-size: 15px; line-height: 1.6; opacity: 0.95; max-width: 640px;">Si eres representante de alguno de los pueblos de la comarca, reg&iacute;strate en Aliste.es para mantener actualizada la informaci&oacute;n de tu pueblo: noticias, eventos, puntos de inter&eacute;s y mucho m&aacute;s.</p>
                <p style="margin: 0; font-size: 15px; line-height: 1.6; opacity: 0.95; max-width: 640px;">A&ntilde;ade las fiestas de tu pueblo al calendario y contacta con nosotros &iexcl;para que te hagamos Redactor!</p>
                <div><a style="display: inline-block; background: white; color: oklch(55% 0.14 130); padding: 10px 24px; border-radius: 999px; font-weight: bold; font-size: 14px; text-decoration: none; margin-top: 8px;" href="../registro"> Reg&iacute;strate aqu&iacute; </a></div>
                </div>
                HTML,
        ]);
    }
}
