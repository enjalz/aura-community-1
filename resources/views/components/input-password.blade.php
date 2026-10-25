{{-- Kolom password dengan ikon mata untuk menampilkan atau menyembunyikan isi (diatur auth.js) --}}
@props(['name', 'label', 'placeholder' => ''])

<div class="field">
    <label for="{{ $name }}" class="field-label">{{ $label }}</label>
    <div class="input-password">
        <input type="password" id="{{ $name }}" name="{{ $name }}" placeholder="{{ $placeholder }}"
               {{ $attributes->class(['input-aura', 'is-invalid' => $errors->has($name)]) }}>
        <button type="button" class="tombol-mata" data-target="{{ $name }}" aria-label="Tampilkan password">
            <i class="bi bi-eye" aria-hidden="true"></i>
        </button>
    </div>
    @error($name)
        <p class="pesan-error">{{ $message }}</p>
    @enderror
</div>