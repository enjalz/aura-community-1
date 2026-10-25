{{-- Kolom input berlabel dengan pesan error.
     Contoh: <x-input name="email" label="Email" type="email" />
     Form ubah: <x-input name="nama" label="Nama" :value="$pengguna->nama" />
     old($name, $value): kalau validasi gagal, isi yang baru diketik dipakai; kalau tidak, nilai awal. --}}
@props(['name', 'label', 'type' => 'text', 'placeholder' => '', 'value' => null])

<div class="field">
    <label for="{{ $name }}" class="field-label">{{ $label }}</label>
    <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $value) }}"
           placeholder="{{ $placeholder }}"
           {{ $attributes->class(['input-aura', 'is-invalid' => $errors->has($name)]) }}>
    @error($name)
        <p class="pesan-error">{{ $message }}</p>
    @enderror
</div>