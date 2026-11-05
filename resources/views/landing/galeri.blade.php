{{-- Bagian 6: Galeri Konten. Tiga GIF tegak 9:16 (format TikTok/Reels).
     Di HP, GIF bisa digeser ke samping. Data diatur di config/landing.php 'galeri'. --}}
<section id="galeri" class="bagian bagian-beige">
    <div class="kontainer">
        <div class="bagian-kepala">
            <h2 class="judul-editorial">Galeri Konten</h2>
            <p>Dokumentasi konten dan kegiatan campaign yang dilakukan bersama Influencer.</p>
        </div>

        <div class="galeri-video">
            @foreach ($landing['galeri'] as $item)
                <figure class="galeri-video-item">
                    @if (filled($item['gambar'] ?? null) && file_exists(public_path($item['gambar'])))
                        {{-- loading="lazy": GIF baru dimuat saat bagian galeri hampir terlihat di layar --}}
                        <img class="galeri-video-isi"
                             src="{{ asset($item['gambar']) }}"
                             alt="{{ $item['label'] ?? 'Galeri konten' }}"
                             loading="lazy">
                    @else
                        {{-- File belum ada: kotak pengganti dengan ukuran yang sama, label cukup di bawah --}}
                        <div class="galeri-video-isi gambar-kosong">
                            <i class="bi bi-image" aria-hidden="true"></i>
                        </div>
                    @endif

                    @if (filled($item['label'] ?? null))
                        <figcaption>{{ $item['label'] }}</figcaption>
                    @endif
                </figure>
            @endforeach
        </div>
    </div>
</section>