{{-- Wireframe Gambar 67 --}}
@extends('layouts.auth')

@section('title', 'Login - Aura Community')
@section('posisi-form', 'auth-wrap-tengah')

@section('tombol-navbar')
    <a href="{{ route('register') }}" class="btn-aura btn-aura-primary btn-aura-sm">Daftar Influencer</a>
@endsection

@section('isi')
    <div class="auth-card">
        <div class="auth-head">
            <h1>Login</h1>
            <p>Masuk dengan email dan password akun Anda.</p>
        </div>

        @if (session('sukses'))
            <div class="alert-aura alert-sukses" role="status">{{ session('sukses') }}</div>
        @endif

        <form method="POST" action="{{ route('login.masuk') }}" class="auth-form" novalidate>
            @csrf
            <x-input name="email" label="Email" type="email" placeholder="email@contoh.com" autocomplete="email" autofocus />
            <x-input-password name="password" label="Password" placeholder="Password" autocomplete="current-password" />

            <button type="submit" class="btn-aura btn-aura-primary btn-aura-besar w-100">Login</button>
        </form>

        <hr class="garis">
        <p class="auth-foot">Belum punya akun? <a href="{{ route('register') }}" class="tautan">Daftar sebagai Influencer</a></p>
    </div>
@endsection