{{-- Footer (wireframe Gambar 65). Latar Charcoal sesuai palet. Empat kolom di layar besar. --}}
<footer class="footer">
    <div class="kontainer">
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <a class="logo-teks" href="#beranda">
                    <img src="{{ asset('images/logo-aura.svg') }}" alt="Aura Community" class="logo-gambar logo-footer">
                </a>
                <p class="footer-deskripsi">{{ $landing['footer']['deskripsi'] }}</p>
            </div>

            <div class="col-6 col-lg-3">
                <p class="footer-judul">Navigasi</p>
                <ul class="footer-daftar">
                    <li><a href="#beranda">Beranda</a></li>
                    <li><a href="#tentang">Tentang Kami</a></li>
                    <li><a href="#layanan">Layanan</a></li>
                    <li><a href="#portofolio">Portofolio</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-3">
                <p class="footer-judul">Eksplorasi</p>
                <ul class="footer-daftar">
                    <li><a href="#testimoni">Testimoni</a></li>
                    <li><a href="#galeri">Galeri Konten</a></li>
                    <li><a href="#kontak">Kontak</a></li>
                </ul>
            </div>

            <div class="col-md-6 col-lg-3">
                <p class="footer-judul">Kontak &amp; Media Sosial</p>
                <ul class="footer-daftar">
                    <li>
                        <a href="{{ $linkWhatsapp }}" @if ($nomorWhatsapp) target="_blank" rel="noopener noreferrer" @endif>
                            <i class="bi bi-whatsapp" aria-hidden="true"></i>
                            {{ $landing['kontak']['whatsappTampil'] }}
                        </a>
                    </li>
                    <li>
                        <a href="https://instagram.com/{{ $landing['kontak']['instagram'] }}" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-instagram" aria-hidden="true"></i>
                            &#64;{{ $landing['kontak']['instagram'] }}
                        </a>
                    </li>
                    <li>
                        <a href="mailto:{{ $landing['kontak']['email'] }}">
                            <i class="bi bi-envelope" aria-hidden="true"></i>
                            {{ $landing['kontak']['email'] }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <p class="hak-cipta">© {{ date('Y') }} Aura Community. All rights reserved.</p>
    </div>
</footer>