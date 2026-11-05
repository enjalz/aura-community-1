{{-- Tombol aksi berbentuk ikon bulat untuk tabel. Keterangan muncul saat kursor diarahkan (title).
     Tautan  : <x-aksi ikon="pencil" label="Ubah" :href="route('...')" />
     Form    : <x-aksi ikon="trash" label="Hapus" :action="route('...')" method="DELETE" konfirmasi="Hapus data ini?" bahaya />
     ikon    = nama ikon Bootstrap Icons tanpa awalan "bi-"
     utama   = warna pink (Terima, Verifikasi)
     bahaya  = merah saat di-hover (Hapus, Tolak, Blacklist)
     baru    = tautan dibuka di tab baru (link konten) --}}
@props([
    'ikon',
    'label',
    'href' => null,
    'action' => null,
    'method' => 'POST',
    'konfirmasi' => null,
    'utama' => false,
    'bahaya' => false,
    'baru' => false,
])

@php
    $kelas = [
        'tombol-aksi',
        'tombol-aksi-utama' => $utama,
        'tombol-aksi-bahaya' => $bahaya,
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" @class($kelas) title="{{ $label }}" aria-label="{{ $label }}"
       @if ($baru) target="_blank" rel="noopener noreferrer" @endif>
        <i class="bi bi-{{ $ikon }}" aria-hidden="true"></i>
    </a>
@else
    <form method="POST" action="{{ $action }}" class="form-sebaris"
          @if ($konfirmasi) data-konfirmasi="{{ $konfirmasi }}" @endif>
        @csrf
        @if (strtoupper($method) !== 'POST')
            @method($method)
        @endif
        <button type="submit" @class($kelas) title="{{ $label }}" aria-label="{{ $label }}">
            <i class="bi bi-{{ $ikon }}" aria-hidden="true"></i>
        </button>
    </form>
@endif