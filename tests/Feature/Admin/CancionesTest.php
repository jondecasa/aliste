<?php

namespace Tests\Feature\Admin;

use App\Models\AudioCancion;
use App\Models\Cancion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;
use Tests\TestCase;

class CancionesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function admin(): User
    {
        return User::factory()->create(['rol' => User::ROL_ADMINISTRADOR]);
    }

    public function test_puede_crear_una_cancion_con_uno_o_varios_audios_descripcion_html_y_letra(): void
    {
        $this->actingAs($this->admin());

        Volt::test('admin.canciones')
            ->call('crear')
            ->set('titulo', 'Ronda de la Aliste')
            ->set('descripcion', '<p>Una <strong>ronda</strong> tradicional.</p>')
            ->set('letra', "Primer verso\nSegundo verso")
            ->set('nuevosAudios', [
                UploadedFile::fake()->create('cara-a.mp3', 500, 'audio/mpeg'),
                UploadedFile::fake()->create('cara-b.mp3', 500, 'audio/mpeg'),
            ])
            ->call('guardar')
            ->assertHasNoErrors();

        $cancion = Cancion::where('titulo', 'Ronda de la Aliste')->firstOrFail();

        $this->assertSame('<p>Una <strong>ronda</strong> tradicional.</p>', $cancion->descripcion);
        $this->assertSame("Primer verso\nSegundo verso", $cancion->letra);
        $this->assertCount(2, $cancion->audios);

        foreach ($cancion->audios as $audio) {
            Storage::disk('public')->assertExists($audio->archivo);
        }
    }

    public function test_seleccionar_un_segundo_audio_no_borra_el_primero(): void
    {
        $this->actingAs($this->admin());

        $componente = Volt::test('admin.canciones')->call('crear');

        // Cada vez que se elige un fichero en el input, el navegador SUSTITUYE
        // la selección anterior por la nueva: simulamos dos elecciones
        // separadas (no un único diálogo con varios ficheros) para comprobar
        // que ambos archivos se acumulan en vez de perderse el primero.
        $componente->set('nuevaSeleccionAudios', [UploadedFile::fake()->create('cara-a.mp3', 500, 'audio/mpeg')]);
        $componente->set('nuevaSeleccionAudios', [UploadedFile::fake()->create('cara-b.mp3', 500, 'audio/mpeg')]);

        $componente->set('titulo', 'Ronda con dos caras');
        $componente->call('guardar')->assertHasNoErrors();

        $cancion = Cancion::where('titulo', 'Ronda con dos caras')->firstOrFail();

        $this->assertCount(2, $cancion->audios);
        $this->assertEqualsCanonicalizing(
            ['cara-a', 'cara-b'],
            $cancion->audios->pluck('titulo')->all()
        );
    }

    public function test_puede_renombrar_un_audio_existente_al_editar(): void
    {
        $this->actingAs($this->admin());

        $cancion = Cancion::create(['titulo' => 'Tonada', 'slug' => 'tonada']);
        $audio = AudioCancion::create([
            'cancion_id' => $cancion->id,
            'archivo' => 'canciones/audios/original.mp3',
            'titulo' => 'Original',
            'orden' => 1,
        ]);

        $componente = Volt::test('admin.canciones')->call('editar', $cancion->id);
        $componente->set("audiosExistentes.0.titulo", 'Grabación de 1998');
        $componente->call('guardar')->assertHasNoErrors();

        $this->assertSame('Grabación de 1998', $audio->fresh()->titulo);
    }

    public function test_puede_eliminar_un_audio_individual_y_borra_el_fichero(): void
    {
        $this->actingAs($this->admin());

        $cancion = Cancion::create(['titulo' => 'Tonada', 'slug' => 'tonada']);
        Storage::disk('public')->put('canciones/audios/borrame.mp3', 'contenido');
        $audio = AudioCancion::create([
            'cancion_id' => $cancion->id,
            'archivo' => 'canciones/audios/borrame.mp3',
            'orden' => 1,
        ]);

        Volt::test('admin.canciones')
            ->call('editar', $cancion->id)
            ->call('eliminarAudio', $audio->id);

        $this->assertNull($audio->fresh());
        Storage::disk('public')->assertMissing('canciones/audios/borrame.mp3');
    }

    public function test_puede_subir_una_foto_de_portada(): void
    {
        $this->actingAs($this->admin());

        Volt::test('admin.canciones')
            ->call('crear')
            ->set('titulo', 'Con portada')
            ->set('nuevaPortada', UploadedFile::fake()->image('portada.jpg', 800, 800))
            ->call('guardar')
            ->assertHasNoErrors();

        $cancion = Cancion::where('titulo', 'Con portada')->firstOrFail();

        $this->assertNotNull($cancion->portada);
        Storage::disk('public')->assertExists($cancion->portada);
    }

    public function test_al_reemplazar_la_portada_se_borra_la_anterior(): void
    {
        $this->actingAs($this->admin());

        $cancion = Cancion::create(['titulo' => 'Tonada', 'slug' => 'tonada', 'portada' => 'canciones/portadas/vieja.webp']);
        Storage::disk('public')->put('canciones/portadas/vieja.webp', 'contenido');

        Volt::test('admin.canciones')
            ->call('editar', $cancion->id)
            ->set('nuevaPortada', UploadedFile::fake()->image('nueva.jpg', 800, 800))
            ->call('guardar')
            ->assertHasNoErrors();

        Storage::disk('public')->assertMissing('canciones/portadas/vieja.webp');
        $this->assertNotSame('canciones/portadas/vieja.webp', $cancion->fresh()->portada);
    }

    public function test_al_eliminar_una_cancion_se_borran_tambien_sus_audios(): void
    {
        $this->actingAs($this->admin());

        $cancion = Cancion::create(['titulo' => 'Tonada', 'slug' => 'tonada']);
        Storage::disk('public')->put('canciones/audios/a.mp3', 'contenido');
        AudioCancion::create([
            'cancion_id' => $cancion->id,
            'archivo' => 'canciones/audios/a.mp3',
            'orden' => 1,
        ]);

        Volt::test('admin.canciones')->call('eliminar', $cancion->id);

        $this->assertNull($cancion->fresh());
        Storage::disk('public')->assertMissing('canciones/audios/a.mp3');
    }
}
