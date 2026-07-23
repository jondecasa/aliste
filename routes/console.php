<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$emailFallo = 'jonapweb@gmail.com';

Schedule::command('notificaciones:eventos-del-dia')
    ->dailyAt('10:00')
    ->description('Notificaciones de eventos del día')
    ->emailOutputOnFailure($emailFallo);

Schedule::command('noticias:scrapear')
    ->twiceDaily(14, 22)
    ->description('Scraper de noticias ZA49')
    ->emailOutputOnFailure($emailFallo);

Schedule::command('sitemap:generar')
    ->dailyAt('03:00')
    ->description('Generación de sitemap.xml')
    ->emailOutputOnFailure($emailFallo);

Schedule::command('backup:base-datos')
    ->cron('0 4 */3 * *')
    ->description('Backup de la base de datos')
    ->emailOutputOnFailure($emailFallo);
