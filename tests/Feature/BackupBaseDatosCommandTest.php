<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class BackupBaseDatosCommandTest extends TestCase
{
    private string $directorio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directorio = storage_path('app/backups');
        File::ensureDirectoryExists($this->directorio);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->directorio);

        parent::tearDown();
    }

    public function test_it_invokes_mysqldump_piped_through_gzip(): void
    {
        Process::fake();

        $this->artisan('backup:base-datos')->assertSuccessful();

        Process::assertRan(function ($process) {
            return str_contains($process->command, 'mysqldump')
                && str_contains($process->command, 'gzip');
        });
    }

    public function test_it_fails_and_cleans_up_when_mysqldump_fails(): void
    {
        Process::fake([
            '*' => Process::result(exitCode: 1, errorOutput: 'mysqldump: error'),
        ]);

        $this->artisan('backup:base-datos')->assertFailed();

        $this->assertEmpty(File::files($this->directorio));
    }

    public function test_it_keeps_only_the_ten_most_recent_backups(): void
    {
        Process::fake();

        foreach (range(1, 12) as $dia) {
            $ruta = $this->directorio."/backup_antiguo_{$dia}.sql.gz";
            File::put($ruta, 'contenido');
            touch($ruta, now()->subDays($dia)->timestamp);
        }

        $this->artisan('backup:base-datos')->assertSuccessful();

        $this->assertCount(10, File::files($this->directorio));
        $this->assertFileDoesNotExist($this->directorio.'/backup_antiguo_11.sql.gz');
        $this->assertFileDoesNotExist($this->directorio.'/backup_antiguo_12.sql.gz');
        $this->assertFileExists($this->directorio.'/backup_antiguo_1.sql.gz');
    }
}
