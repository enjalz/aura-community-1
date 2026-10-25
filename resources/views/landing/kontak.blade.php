{{-- Bagian 7: Kontak (wireframe Gambar 64).
     Dua kartu ajakan: untuk brand (WhatsApp) dan untuk influencer (registrasi). --}}
<section id="kontak" class="bagian">
    <div class="kontainer">
        <div class="bagian-kepala">
            <h2 class="judul-editorial">Hubungi &amp; Bergabung Bersama Kami</h2>
            <p>Pilih jalur kolaborasi yang sesuai dengan kebutuhan Brand Anda atau daftarkan diri Anda sebagai bagian dari komunitas Influencer kami.</p>
        </div>

        <div class="row g-4">
            {{-- Kartu untuk brand: membuka WhatsApp kalau AURA_WHATSAPP diisi di .env --}}
            <div class="col-md-6">
                <article class="kartu">
                    <div class="kartu-isi">
                        <div class="ikon-kartu"><i class="bi bi-briefcase" aria-hidden="true"></i></div>
                        <h3>Untuk Brand &amp; Mitra</h3>
                        <p>Tertarik bekerja sama untuk campaign brand Anda? Konsultasikan strategi influencer marketing Anda langsung bersama tim Aura Community.</p>
                        <a href="{{ $linkWhatsapp }}" class="btn-aura btn-aura-primary kartu-tombol"
                           @if ($nomorWhatsapp) target="_blank" rel="noopener noreferrer" @endif>
                            <i class="bi bi-whatsapp" aria-hidden="true"></i>
                            Hubungi via WhatsApp
                        </a>
                    </div>
                </article>
            </div>

            {{-- Kartu untuk influencer: menuju halaman registrasi (Tabel 5) --}}
            <div class="col-md-6">
                <article class="kartu">
                    <div class="kartu-isi">
                        <div class="ikon-kartu"><i class="bi bi-person-plus" aria-hidden="true"></i></div>
                        <h3>Ingin Bergabung Menjadi Influencer?</h3>
                        <p>Kembangkan potensi dan dapatkan kesempatan kolaborasi campaign bersama berbagai brand dalam jaringan Aura Community.</p>
                        <a href="{{ route('register') }}" class="btn-aura btn-aura-outline kartu-tombol">
                            <i class="bi bi-person-plus" aria-hidden="true"></i>
                            Registrasi sebagai Influencer
                        </a>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>