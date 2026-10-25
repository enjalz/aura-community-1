{{-- Bagian 5: Testimoni (wireframe Gambar 62). Avatar berisi huruf pertama nama klien. --}}
<section id="testimoni" class="bagian">
    <div class="kontainer">
        <div class="bagian-kepala">
            <h2 class="judul-editorial">Testimoni</h2>
            <p>Feedback dan ulasan kerja sama dari mitra dan client.</p>
        </div>

        <div class="row g-4">
            @foreach ($landing['testimoni'] as $testimoni)
                <div class="col-md-6 col-lg-4">
                    <article class="kartu">
                        <div class="kartu-isi">
                            <div class="testi-orang">
                                <div class="testi-avatar" aria-hidden="true">{{ mb_substr($testimoni['nama'], 0, 1) }}</div>
                                <div>
                                    <strong class="testi-nama">{{ $testimoni['nama'] }}</strong>
                                    <span class="testi-perusahaan">{{ $testimoni['perusahaan'] }}</span>
                                </div>
                            </div>

                            {{-- Bintang penuh sebanyak rating, sisanya bintang kosong --}}
                            <div class="testi-bintang" aria-label="Rating {{ $testimoni['rating'] }} dari 5">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="bi {{ $i <= $testimoni['rating'] ? 'bi-star-fill' : 'bi-star' }}" aria-hidden="true"></i>
                                @endfor
                            </div>

                            <blockquote class="testi-kutipan">“{{ $testimoni['isi'] }}”</blockquote>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>