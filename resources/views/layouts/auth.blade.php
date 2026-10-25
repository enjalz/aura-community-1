{{-- Kerangka halaman Login dan Registrasi (wireframe Gambar 66, 67) --}}
@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
@endpush

{{-- password.js = ikon mata untuk menampilkan atau menyembunyikan password --}}
@push('scripts')
    <script src="{{ asset('js/password.js') }}"></script>
@endpush

@section('content')
    <div class="auth-halaman">
        <header class="auth-navbar">
            <a href="{{ route('beranda') }}" class="logo-teks">Aura Community</a>
            @yield('tombol-navbar')
        </header>

        <main class="auth-wrap @yield('posisi-form')">
            @yield('isi')
        </main>
    </div>
@endsection