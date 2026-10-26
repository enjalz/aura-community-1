{{-- Kerangka semua dashboard: sidebar, topbar, dan isi halaman.
     Halaman yang memakainya mengisi @section('judul') dan @section('isi'). --}}
@extends('layouts.app')

{{-- dashboard.css = kerangka (sidebar, topbar).
     konten.css = isi halaman yang dipakai berulang (tabel, paginasi, panel, formulir).
     @prepend menaruhnya sebelum CSS khusus halaman, supaya halaman masih bisa menimpanya kalau perlu. --}}
@prepend('styles')
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    <link href="{{ asset('css/konten.css') }}" rel="stylesheet">
@endprepend

{{-- konfirmasi.js = dialog konfirmasi untuk form bertanda data-konfirmasi
     password.js   = ikon mata di kolom password (form tambah/ubah influencer, owner brand, dan profil) --}}
@push('scripts')
    <script src="{{ asset('js/konfirmasi.js') }}"></script>
    <script src="{{ asset('js/password.js') }}"></script>
@endpush

@section('content')
    @php
        $pengguna = auth()->user();
        $peran = $pengguna->peran();
        $menu = config('menu.' . $peran, []);

        // Influencer yang belum diverifikasi hanya bisa membuka menu bertanda 'selalu'
        $terkunci = $peran === 'influencer' && $pengguna->influencer->statusVerifikasi !== 'diverifikasi';

        $labelPeran = ['admin' => 'Admin', 'owner_brand' => 'Owner Brand', 'influencer' => 'Influencer'][$peran] ?? '';
    @endphp

    <div class="dash">
        <div class="sidebar-wrap">
            {{-- offcanvas-lg: tampil tetap di layar besar, menjadi panel geser di layar kecil --}}
            <aside class="offcanvas-lg offcanvas-start sidebar" tabindex="-1" id="sidebar" aria-label="Menu samping">
                <div class="sidebar-isi">
                    <div class="d-flex justify-content-end d-lg-none">
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Tutup menu"></button>
                    </div>

                    <a href="{{ route('dashboard') }}" class="sidebar-logo">
                        <img src="{{ asset('images/logo-aura.svg') }}" alt="Aura Community" class="logo-gambar">
                    </a>

                    <nav class="sidebar-menu" aria-label="Menu utama">
                        @foreach ($menu as $item)
                            @isset($item['grup'])
                                <p class="sidebar-grup">{{ $item['grup'] }}</p>
                                @continue
                            @endisset

                            @php
                                $bisaDibuka = Route::has($item['route']) && (! $terkunci || ! empty($item['selalu']));
                                $aktif = request()->routeIs($item['route'], $item['route'] . '.*');
                            @endphp

                            @if ($bisaDibuka)
                                <a href="{{ route($item['route']) }}" @class(['menu-item', 'is-active' => $aktif]) @if ($aktif) aria-current="page" @endif>
                                    <i class="bi bi-{{ $item['ikon'] }}" aria-hidden="true"></i>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @else
                                <span class="menu-item is-disabled" aria-disabled="true">
                                    <i class="bi bi-{{ $item['ikon'] }}" aria-hidden="true"></i>
                                    <span>{{ $item['label'] }}</span>
                                </span>
                            @endif
                        @endforeach
                    </nav>

                    <form method="POST" action="{{ route('logout') }}" class="sidebar-logout">
                        @csrf
                        <button type="submit" class="btn-aura btn-aura-outline w-100">
                            <i class="bi bi-box-arrow-left" aria-hidden="true"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </aside>
        </div>

        <div class="dash-main">
            <header class="topbar">
                <div class="topbar-kiri">
                    <button type="button" class="tombol-menu d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Buka menu">
                        <i class="bi bi-list" aria-hidden="true"></i>
                    </button>
                    <h1 class="topbar-judul">@yield('judul')</h1>
                </div>

                <div class="pengguna-info">
                    <div class="pengguna-teks">
                        <div class="pengguna-nama">{{ $pengguna->nama }}</div>
                        <div class="pengguna-peran">{{ $labelPeran }}</div>
                    </div>
                    <div class="avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($pengguna->nama, 0, 1)) }}</div>
                </div>
            </header>

            <main class="dash-konten">
                @if (session('sukses'))
                    <div class="alert-aura alert-sukses" role="status">{{ session('sukses') }}</div>
                @endif
                @if (session('gagal'))
                    <div class="alert-aura alert-gagal" role="alert">{{ session('gagal') }}</div>
                @endif

                @yield('isi')
            </main>
        </div>
    </div>
@endsection