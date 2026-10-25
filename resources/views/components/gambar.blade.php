{{-- Menampilkan foto kalau file-nya sudah ada di folder public.
     Kalau belum ada, tampil kotak beige berlabel (seperti placeholder di wireframe).
     Contoh: <x-gambar src="images/hero.jpg" label="Gambar Hero" class="hero-gambar" />
     Class tambahan (misalnya hero-gambar) diatur ukurannya di landing.css. --}}
@props(['src' => null, 'label'])

@if ($src && file_exists(public_path($src)))
    <img src="{{ asset($src) }}" alt="{{ $label }}" loading="lazy" {{ $attributes->class('gambar') }}>
@else
    <div role="img" aria-label="{{ $label }}" {{ $attributes->class(['gambar', 'gambar-kosong']) }}>
        <i class="bi bi-image" aria-hidden="true"></i>
        <span>{{ $label }}</span>
    </div>
@endif 