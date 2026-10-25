{{-- Bagian 4: Portofolio (wireframe Gambar 61). Tiga kartu: gambar di atas, teks, lalu jenis campaign dan jumlah influencer. --}}
<section id="portofolio" class="bagian bagian-beige">
    <div class="kontainer">
        <div class="bagian-kepala">
            <h2 class="judul-editorial">Portofolio</h2>
            <p>Dokumentasi kegiatan dan campaign yang telah dijalankan bersama Influencer.</p>
        </div>

        <div class="row g-4">
            @foreach ($landing['portofolio'] as $portofolio)
                <div class="col-md-6 col-lg-4">
                    <article class="kartu">
                        <x-gambar :src="$portofolio['gambar']" label="Gambar Campaign" class="porto-gambar" />
                        <div class="kartu-isi">
                            <h3>{{ $portofolio['judul'] }}</h3>
                            <p>{{ $portofolio['deskripsi'] }}</p>
                            <div class="porto-kaki">
                                <strong>{{ $portofolio['jenis'] }}</strong>
                                <span>{{ $portofolio['jumlahInfluencer'] }} Influencer</span>
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>