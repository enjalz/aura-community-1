{{-- Bagian 3: Layanan (wireframe Gambar 60). Dua kartu berdampingan. --}}
<section id="layanan" class="bagian">
    <div class="kontainer">
        <div class="bagian-kepala">
            <h2 class="judul-editorial">Layanan</h2>
            <p>Layanan yang ditawarkan Aura Community dalam mendukung kebutuhan promosi melalui media sosial dan Influencer.</p>
        </div>

        <div class="row g-4">
            @foreach ($landing['layanan'] as $layanan)
                <div class="col-md-6">
                    <article class="kartu">
                        <div class="kartu-isi">
                            <div class="ikon-kartu">
                                <i class="bi bi-{{ $layanan['ikon'] }}" aria-hidden="true"></i>
                            </div>
                            <h3>{{ $layanan['judul'] }}</h3>
                            <p>{{ $layanan['deskripsi'] }}</p>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>