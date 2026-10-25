{{-- Label status berwarna. Teks diambil dari config/aura.php.
     Contoh: <x-status jenis="statusDraft" :nilai="$hasil->statusDraft" />
     Class CSS memakai tanda strip: belum_dikirim menjadi badge-belum-dikirim. --}}
@props(['jenis', 'nilai'])

<span class="badge-status badge-{{ str_replace('_', '-', $nilai) }}">{{ config("aura.{$jenis}.{$nilai}") }}</span>