{{-- Input demografi audiens untuk satu platform (subbab 2.1.4).
     Variabel: $kunci (instagram/tiktok), $nama (Instagram/TikTok), $akun (AkunSosmed atau null).
     Dipakai di registrasi, profil influencer, dan form data influencer Admin.
     Diletakkan di dalam <div class="row g-3">. --}}
@php($akun = $akun ?? null)

<div class="col-12">
    <p class="teks-bantuan mb-0">Demografi audiens {{ $nama }}, lihat di menu insight atau analitik aplikasi {{ $nama }}.</p>
</div>

<div class="col-md-4">
    <x-input :name="$kunci . '_domisili'" label="Domisili Audiens" :value="$akun?->domisiliAudiens" placeholder="Contoh: Jakarta" />
</div>

<div class="col-md-4">
    <div class="field">
        <label for="{{ $kunci }}_usia" class="field-label">Usia Dominan</label>
        <select id="{{ $kunci }}_usia" name="{{ $kunci }}_usia" @class(['input-aura', 'is-invalid' => $errors->has($kunci . '_usia')])>
            <option value="" @selected(! old($kunci . '_usia', $akun?->usiaDominan))>Pilih usia</option>
            @foreach (config('aura.rentangUsia') as $nilai => $label)
                <option value="{{ $nilai }}" @selected(old($kunci . '_usia', $akun?->usiaDominan) === (string) $nilai)>{{ $label }}</option>
            @endforeach
        </select>
        @error($kunci . '_usia')
            <p class="pesan-error">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="col-md-4">
    <div class="field">
        <label for="{{ $kunci }}_gender" class="field-label">Gender Dominan</label>
        <select id="{{ $kunci }}_gender" name="{{ $kunci }}_gender" @class(['input-aura', 'is-invalid' => $errors->has($kunci . '_gender')])>
            <option value="" @selected(! old($kunci . '_gender', $akun?->genderDominan))>Pilih gender</option>
            @foreach (config('aura.genderAudiens') as $nilai => $label)
                <option value="{{ $nilai }}" @selected(old($kunci . '_gender', $akun?->genderDominan) === $nilai)>{{ $label }}</option>
            @endforeach
        </select>
        @error($kunci . '_gender')
            <p class="pesan-error">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="col-12">
    <div class="field">
        <label for="{{ $kunci }}_demografi" class="field-label">Screenshot Insight Audiens</label>
        <input type="file" id="{{ $kunci }}_demografi" name="{{ $kunci }}_demografi" accept="image/png,image/jpeg,image/webp"
               @class(['input-aura', 'is-invalid' => $errors->has($kunci . '_demografi')])>
        <p class="teks-bantuan">
            @if ($akun?->fileDemografi)
                Screenshot saat ini:
                <a class="tautan" href="{{ route('berkas.demografi', $akun) }}" target="_blank" rel="noopener noreferrer">lihat</a>.
                Unggah ulang jika data demografi diubah.
            @else
                Format JPG, PNG, atau WEBP, maksimal 5 MB.
            @endif
        </p>
        @error($kunci . '_demografi')
            <p class="pesan-error">{{ $message }}</p>
        @enderror
    </div>
</div>