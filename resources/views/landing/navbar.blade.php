{{-- Navbar landing page (wireframe Gambar 58).
     navbar-expand-xl: menu tampil berjajar di layar lebar, dan menjadi menu lipat (hamburger) di bawah 1200px.
     Menu lipat dibuka-tutup oleh JavaScript Bootstrap (sudah dimuat di layouts/app.blade.php). --}}
@php
    // id bagian => label menu. Tautannya menggulir ke elemen dengan id yang sama.
    $menuLanding = [
        'beranda' => 'Beranda',
        'tentang' => 'Tentang Kami',
        'layanan' => 'Layanan',
        'portofolio' => 'Portofolio',
        'testimoni' => 'Testimoni',
        'galeri' => 'Galeri Konten',
        'kontak' => 'Kontak',
    ];
@endphp

<nav class="navbar navbar-expand-xl navbar-landing" aria-label="Menu utama">
    <div class="kontainer">
        <a class="logo-teks" href="#beranda">Aura Community</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu-landing"
                aria-controls="menu-landing" aria-expanded="false" aria-label="Buka menu">
            <i class="bi bi-list" aria-hidden="true"></i>
        </button>

        <div class="collapse navbar-collapse" id="menu-landing">
            <ul class="navbar-nav mx-auto">
                @foreach ($menuLanding as $idBagian => $label)
                    <li class="nav-item">
                        <a @class(['nav-link', 'active' => $loop->first]) href="#{{ $idBagian }}">{{ $label }}</a>
                    </li>
                @endforeach
            </ul>

            {{-- Guest melihat Masuk dan Daftar Influencer. Pengguna yang sudah login melihat tombol Dashboard. --}}
            <div class="navbar-aksi">
                @guest
                    <a href="{{ route('login') }}" class="btn-aura btn-aura-outline btn-aura-sm">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-aura btn-aura-primary btn-aura-sm">Daftar Influencer</a>
                @else
                    <a href="{{ route('dashboard') }}" class="btn-aura btn-aura-primary btn-aura-sm">Dashboard</a>
                @endguest
            </div>
        </div>
    </div>
</nav>