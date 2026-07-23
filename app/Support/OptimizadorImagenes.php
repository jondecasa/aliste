<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class OptimizadorImagenes
{
    /**
     * Redimensiona (sin ampliar) y comprime a WebP una imagen subida, quitando
     * los metadatos EXIF, y la guarda en el disco indicado.
     *
     * @return string Ruta relativa guardada, para almacenar en BD.
     */
    public static function guardar(
        UploadedFile $archivo,
        string $carpeta,
        string $disco = 'public',
        int $anchoMaximo = 1920,
        int $calidad = 82,
    ): string {
        $imagen = Image::read($archivo)->scaleDown(width: $anchoMaximo);

        $ruta = trim($carpeta, '/').'/'.Str::random(40).'.webp';

        Storage::disk($disco)->put($ruta, (string) $imagen->toWebp(quality: $calidad, strip: true));

        return $ruta;
    }
}
