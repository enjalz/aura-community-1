{{-- Kerangka halaman Login dan Registrasi (wireframe Gambar 66, 67).
     Navbar memakai .kontainer yang sama dengan navbar landing page,
     supaya posisi logo dan tombol tidak bergeser saat berpindah halaman.
     Navbar menempel di atas dan menjadi ramping saat digulir, sama seperti landing page. --}}
@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
@endpush

{{-- password.js = ikon mata untuk menampilkan atau menyembunyikan password --}}
@push('scripts')
    <script src="{{ asset('js/password.js') }}"></script>
    <script>
        // Navbar menjadi ramping dan transparan setelah halaman digulir (class is-tergulir di auth.css)
        document.addEventListener('DOMContentLoaded', function () {
            var navbar = document.querySelector('.auth-navbar');

            if (! navbar) {
                return;
            }

            function perbarui() {
                navbar.classList.toggle('is-tergulir', window.scrollY > 10);
            }

            perbarui();
            window.addEventListener('scroll', perbarui, { passive: true });
        });
    </script>
@endpush

@section('content')
    <div class="auth-halaman">
        <header class="auth-navbar">
            <div class="kontainer">
                <a href="{{ route('beranda') }}" class="logo-teks">
                    <img src="{{ asset('images/logo-aura.svg') }}" alt="Aura Community" class="logo-gambar">
                </a>
                <div class="navbar-aksi">
                    @yield('tombol-navbar')
                </div>
            </div>
        </header>

        <main class="auth-wrap @yield('posisi-form')">
            @yield('isi')
        </main>
    </div>
@endsection