<?php

namespace App\Console\Commands;

use App\Services\BlacklistOtomatis;
use Illuminate\Console\Command;

class BlacklistOtomatisCommand extends Command
{
    protected $signature = 'aura:blacklist-otomatis';

    protected $description = 'Memasukkan influencer yang tidak mengirim konten hingga campaign berakhir ke daftar blacklist';

    public function handle(BlacklistOtomatis $blacklist): int
    {
        $jumlah = $blacklist->jalankan();

        $this->info("{$jumlah} influencer dimasukkan ke daftar blacklist.");

        return self::SUCCESS;
    }
}