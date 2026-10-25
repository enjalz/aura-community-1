{{-- Bagian 2: Tentang Aura Community (wireframe Gambar 59). Latar beige sebagai pembeda bagian. --}}
<section id="tentang" class="bagian bagian-beige">
    <div class="kontainer">
        <div class="bagian-kepala">
            <h2 class="judul-editorial">Tentang Aura Community</h2>
        </div>

        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <x-gambar :src="$landing['tentang']['gambar']" label="Gambar Tentang Aura Community" class="tentang-gambar" />
            </div>

            <div class="col-lg-6">
                <p class="tentang-utama">{{ $landing['tentang']['paragrafUtama'] }}</p>
                <p class="teks-redup">{{ $landing['tentang']['paragrafKedua'] }}</p>

                {{-- Kotak angka statistik --}}
                <div class="statistik">
                    @foreach ($landing['tentang']['statistik'] as $statistik)
                        <div class="statistik-item">
                            <strong>{{ $statistik['nilai'] }}</strong>
                            <span>{{ $statistik['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>