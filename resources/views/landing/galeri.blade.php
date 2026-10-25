{{-- Bagian 6: Galeri Konten (wireframe Gambar 63). Enam gambar, tiga per baris di layar besar. --}}
<section id="galeri" class="bagian bagian-beige">
    <div class="kontainer">
        <div class="bagian-kepala">
            <h2 class="judul-editorial">Galeri Konten</h2>
            <p>Dokumentasi konten dan kegiatan campaign yang dilakukan bersama Influencer.</p>
        </div>

        <div class="row g-4">
            @foreach ($landing['galeri'] as $gambar)
                <div class="col-6 col-lg-4">
                    <x-gambar :src="$gambar" label="Foto / Video" class="galeri-gambar" />
                </div>
            @endforeach
        </div>
    </div>
</section>