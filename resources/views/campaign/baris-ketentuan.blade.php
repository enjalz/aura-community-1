{{-- Satu baris ketentuan platform di form campaign.
     Dipakai dua kali di form.blade.php:
     1. Untuk setiap ketentuan yang sudah ada (indeks 0, 1, 2, ...)
     2. Di dalam <template> sebagai cetakan baris baru, dengan indeks "{indeks}" yang diganti oleh ketentuan.js --}}
@php
    $nama = "ketentuan[{$indeks}]";        // nama field, contoh: ketentuan[0][platform]
    $kunciError = "ketentuan.{$indeks}";   // kunci error, contoh: ketentuan.0.platform
@endphp

<div class="baris-ketentuan" data-baris-ketentuan>
    <div class="field">
        <label class="field-label">Platform</label>
        <select name="{{ $nama }}[platform]" @class(['input-aura', 'is-invalid' => $errors->has("{$kunciError}.platform")])>
            @foreach (config('aura.platform') as $platform)
                <option value="{{ $platform }}" @selected(($baris['platform'] ?? '') === $platform)>{{ $platform }}</option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label class="field-label">Jenis Konten</label>
        <select name="{{ $nama }}[jenisKonten]" @class(['input-aura', 'is-invalid' => $errors->has("{$kunciError}.jenisKonten")])>
            @foreach (config('aura.jenisKonten') as $nilai => $label)
                <option value="{{ $nilai }}" @selected(($baris['jenisKonten'] ?? '') === $nilai)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label class="field-label">Jumlah</label>
        <input type="number" name="{{ $nama }}[jumlahPostingan]" value="{{ $baris['jumlahPostingan'] ?? 1 }}" min="1" max="100"
               @class(['input-aura', 'is-invalid' => $errors->has("{$kunciError}.jumlahPostingan")])>
    </div>

    <button type="button" class="tombol-ikon" data-hapus-ketentuan aria-label="Hapus ketentuan ini">
        <i class="bi bi-trash" aria-hidden="true"></i>
    </button>

    @if ($errors->has("{$kunciError}.*"))
        <p class="pesan-error baris-ketentuan-error">{{ $errors->first("{$kunciError}.*") }}</p>
    @endif
</div>