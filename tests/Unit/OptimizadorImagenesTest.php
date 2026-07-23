<?php

namespace Tests\Unit;

use App\Support\OptimizadorImagenes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OptimizadorImagenesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_it_scales_down_large_images_and_converts_to_webp(): void
    {
        $archivo = UploadedFile::fake()->image('foto.jpg', 4000, 3000);

        $ruta = OptimizadorImagenes::guardar($archivo, 'test');

        $this->assertStringEndsWith('.webp', $ruta);
        Storage::disk('public')->assertExists($ruta);

        $info = getimagesizefromstring(Storage::disk('public')->get($ruta));

        $this->assertSame(1920, $info[0]);
        $this->assertLessThan(3000, $info[1]);
        $this->assertSame('image/webp', $info['mime']);
    }

    public function test_it_does_not_upscale_small_images(): void
    {
        $archivo = UploadedFile::fake()->image('foto.jpg', 300, 200);

        $ruta = OptimizadorImagenes::guardar($archivo, 'test');

        $info = getimagesizefromstring(Storage::disk('public')->get($ruta));

        $this->assertSame(300, $info[0]);
        $this->assertSame(200, $info[1]);
    }

    public function test_it_respects_a_custom_maximum_width(): void
    {
        $archivo = UploadedFile::fake()->image('avatar.jpg', 2000, 2000);

        $ruta = OptimizadorImagenes::guardar($archivo, 'avatars', anchoMaximo: 512);

        $info = getimagesizefromstring(Storage::disk('public')->get($ruta));

        $this->assertSame(512, $info[0]);
        $this->assertSame(512, $info[1]);
    }
}
