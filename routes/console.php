<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Blacklist otomatis influencer yang tidak mengirim konten hingga campaign berakhir.
// Dicek setiap hari pukul 00.05 (kalau scheduler dijalankan).
Schedule::command('aura:blacklist-otomatis')->dailyAt('00:05');