{{-- Bagian 1: Hero (wireframe Gambar 58). Teks di kiri, gambar di kanan.
     Bootstrap grid: col-lg-6 = setengah lebar di layar besar, penuh di layar kecil. --}}
<section id="beranda" class="hero">
    <div class="kontainer">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                {{-- span.sorot = kata yang diberi "stabilo" pink (aksen dekoratif dari palet) --}}
                <h1 class="hero-judul">
                    {{ $landing['hero']['judulAwal'] }}
                    <span class="sorot">{{ $landing['hero']['judulSorot'] }}</span>
                </h1>
                <p class="hero-deskripsi">{{ $landing['hero']['deskripsi'] }}</p>

                <div class="hero-aksi">
                    <a href="{{ $linkWhatsapp }}" class="btn-aura btn-aura-primary btn-aura-besar"
                       @if ($nomorWhatsapp) target="_blank" rel="noopener noreferrer" @endif>
                        Hubungi Aura Community
                    </a>
                    <a href="#layanan" class="btn-aura btn-aura-outline btn-aura-besar">Lihat Layanan</a>
                </div>
            </div>

            <div class="col-lg-6">
                <x-gambar :src="$landing['hero']['gambar']" label="Gambar Hero" class="hero-gambar" />
            </div>
        </div>
    </div>
</section>