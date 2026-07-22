<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('notificaciones:eventos-del-dia')->dailyAt('10:00');

Schedule::command('noticias:scrapear')->twiceDaily(14, 22);
