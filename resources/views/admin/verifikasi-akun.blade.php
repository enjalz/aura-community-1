{{-- Verifikasi Akun.
     Atas kiri: daftar akun influencer baru. Atas kanan: data influencer yang sedang diperiksa,
     termasuk demografi audiens dan screenshot insight untuk dicocokkan (Tabel 8 langkah 5).
     Bawah: akun media sosial yang ditambah atau diubah influencer setelah akunnya terverifikasi.
     Pesan sukses/gagal setelah Verifikasi/Tolak ditampilkan oleh layouts/dashboard.blade.php. --}}
@extends('layouts.dashboard')

@section('title', 'Verifikasi Akun - Aura Community')
@section('judul', 'Verifikasi Akun')
@push('scripts')
    <script>
        // Saat ikon Tolak diklik: isi alamat form dan nama akun di pop-up, lalu kosongkan alasan sebelumnya
        document.getElementById('modalTolakSosmed')?.addEventListener('show.bs.modal', function (event) {
            const tombol = event.relatedTarget;
            const form = this.querySelector('form');

            form.action = tombol.dataset.action;
            this.querySelector('[data-nama-akun]').textContent = tombol.dataset.nama;
            form.querySelector('textarea').value = '';
        });

        // Kursor langsung berada di kolom alasan setelah pop-up terbuka
        document.getElementById('modalTolakSosmed')?.addEventListener('shown.bs.modal', function () {
            this.querySelector('textarea').focus();
        });
    </script>
@endpush

@section('isi')
    <div class="dua-kolom">

        {{-- ===== KIRI: DAFTAR ===== --}}
        <section class="kolom-utama">
            {{-- total() = jumlah semua akun menunggu, bukan hanya yang tampil di halaman ini --}}
            <h2 class="judul-bagian">Akun Influencer Menunggu Verifikasi ({{ $daftar->total() }})</h2>

            @if ($daftar->isEmpty())
                <div class="kosong">Tidak ada akun influencer yang menunggu verifikasi.</div>
            @else
                <div class="tabel-wrap">
                    <table class="tabel-aura">
                        <thead>
                            <tr>
                                <th scope="col">Nama</th>
                                <th scope="col">Email</th>
                                <th scope="col">Kategori</th>
                                <th scope="col" class="kolom-aksi">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($daftar as $influencer)
                                {{-- is() = apakah baris ini influencer yang sedang tampil di panel kanan --}}
                                <tr @class(['is-terpilih' => $terpilih?->is($influencer)])>
                                    <td>{{ $influencer->pengguna->nama }}</td>
                                    <td>{{ $influencer->pengguna->email }}</td>
                                    <td>{{ $influencer->kategori }}</td>
                                    <td class="kolom-aksi">
                                        {{-- Menambah ?pilih=ID tanpa menghapus nomor halaman (?page=2) --}}
                                        <x-aksi ikon="eye" label="Periksa" :href="request()->fullUrlWithQuery(['pilih' => $influencer->idPengguna])" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $daftar->links('partials.paginasi', ['label' => 'akun']) }}
            @endif
        </section>

        {{-- ===== KANAN: DATA INFLUENCER ===== --}}
        <aside class="kolom-samping">
            <h2 class="judul-bagian">Data Influencer</h2>

            @if ($terpilih)
                {{-- daftar-data dari dashboard.css; daftar-data-tumpuk (konten.css) membuat label di atas nilai --}}
                <dl class="daftar-data daftar-data-tumpuk">
                    <div class="daftar-data-baris"><dt>Nama</dt><dd>{{ $terpilih->pengguna->nama }}</dd></div>
                    <div class="daftar-data-baris"><dt>Email</dt><dd>{{ $terpilih->pengguna->email }}</dd></div>
                    <div class="daftar-data-baris"><dt>No. Telepon</dt><dd>{{ $terpilih->pengguna->noTelepon }}</dd></div>
                    <div class="daftar-data-baris"><dt>Alamat</dt><dd>{{ $terpilih->pengguna->alamat }}</dd></div>
                    <div class="daftar-data-baris"><dt>Kategori</dt><dd>{{ $terpilih->kategori }}</dd></div>

                    {{-- Satu baris per platform (Instagram, TikTok) dari config/aura.php --}}
                    @foreach (config('aura.platform') as $namaPlatform)
                        @php($akun = $terpilih->akunSosmed->firstWhere('platform', $namaPlatform))
                        <div class="daftar-data-baris">
                            <dt>Akun {{ $namaPlatform }}</dt>
                            <dd>
                                @if ($akun)
                                    &#64;{{ $akun->username }} ({{ number_format($akun->jumlahPengikut, 0, ',', '.') }} pengikut)
                                    {{-- Link hanya bisa diklik kalau diawali http:// atau https://,
                                         supaya tautan berbahaya seperti "javascript:..." tidak bisa dijalankan --}}
                                    @if (\Illuminate\Support\Str::startsWith($akun->linkProfil, ['https://', 'http://']))
                                        - <a class="tautan" href="{{ $akun->linkProfil }}" target="_blank" rel="noopener noreferrer">Lihat profil</a>
                                    @endif

                                    {{-- Tabel 8 langkah 5: cocokkan data demografi dengan screenshot insight --}}
                                    <div class="teks-redup">
                                        Audiens: {{ $akun->domisiliAudiens ?? '-' }}
                                        · {{ $akun->usiaDominan ? config('aura.rentangUsia.' . $akun->usiaDominan) : '-' }}
                                        · {{ $akun->genderDominan ? config('aura.genderAudiens.' . $akun->genderDominan) : '-' }}
                                    </div>
                                    @if ($akun->fileDemografi)
                                        <a class="tautan" href="{{ route('berkas.demografi', $akun) }}" target="_blank" rel="noopener noreferrer">Lihat screenshot insight audiens</a>
                                    @else
                                        <span class="teks-redup">Screenshot insight audiens belum diunggah</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>

                {{-- Dua form POST kecil. @csrf = token keamanan wajib untuk setiap form POST.
                     data-konfirmasi = pertanyaan yang ditampilkan konfirmasi.js sebelum form dikirim. --}}
                <div class="panel-aksi">
                    <form method="POST" action="{{ route('admin.verifikasi.tolak', $terpilih) }}"
                          data-konfirmasi="Tolak akun {{ $terpilih->pengguna->nama }}?">
                        @csrf
                        <button type="submit" class="btn-aura btn-aura-outline w-100">Tolak</button>
                    </form>

                    <form method="POST" action="{{ route('admin.verifikasi.setujui', $terpilih) }}"
                          data-konfirmasi="Verifikasi akun {{ $terpilih->pengguna->nama }}?">
                        @csrf
                        <button type="submit" class="btn-aura btn-aura-primary w-100">Verifikasi</button>
                    </form>
                </div>
            @else
                <div class="kosong">Pilih akun di daftar untuk melihat datanya.</div>
            @endif
        </aside>
    </div>

    {{-- ===== AKUN MEDIA SOSIAL BARU ATAU DIUBAH ===== --}}
    <section class="mt-4">
        <h2 class="judul-bagian">Akun Media Sosial Menunggu Verifikasi ({{ $akunMenunggu->count() }})</h2>
        <p class="teks-bantuan">
            Akun yang ditambah atau diubah influencer setelah akunnya terverifikasi.
            Cocokkan jumlah pengikut dan data audiens dengan profil serta screenshot insight sebelum memverifikasi.
        </p>

        @error('alasan')
            <div class="alert-aura alert-gagal" role="alert">{{ $message }}</div>
        @enderror

        @if ($akunMenunggu->isEmpty())
            <div class="kosong">Tidak ada akun media sosial yang menunggu verifikasi.</div>
        @else
            <div class="tabel-wrap">
                <table class="tabel-aura">
                    <thead>
                        <tr>
                            <th scope="col">Influencer</th>
                            <th scope="col">Akun</th>
                            <th scope="col">Pengikut</th>
                            <th scope="col">Lokasi / Usia / Gender</th>
                            <th scope="col">Bukti</th>
                            <th scope="col" class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($akunMenunggu as $akunSosmed)
                            <tr>
                                <td>{{ $akunSosmed->influencer->pengguna->nama }}</td>
                                <td>{{ $akunSosmed->platform }} - &#64;{{ $akunSosmed->username }}</td>
                                <td>{{ number_format($akunSosmed->jumlahPengikut, 0, ',', '.') }}</td>
                                <td>
                                    {{ $akunSosmed->domisiliAudiens ?? '-' }}
                                    / {{ $akunSosmed->usiaDominan ? config('aura.rentangUsia.' . $akunSosmed->usiaDominan) : '-' }}
                                    / {{ $akunSosmed->genderDominan ? config('aura.genderAudiens.' . $akunSosmed->genderDominan) : '-' }}
                                </td>
                                <td>
                                    @if (\Illuminate\Support\Str::startsWith($akunSosmed->linkProfil, ['https://', 'http://']))
                                        <a class="tautan" href="{{ $akunSosmed->linkProfil }}" target="_blank" rel="noopener noreferrer">Profil</a>
                                    @endif
                                    @if ($akunSosmed->fileDemografi)
                                        · <a class="tautan" href="{{ route('berkas.demografi', $akunSosmed) }}" target="_blank" rel="noopener noreferrer">Screenshot</a>
                                    @else
                                        · <span class="teks-redup">Screenshot belum diunggah</span>
                                    @endif
                                </td>
                                <td class="kolom-aksi">
                                    <div class="aksi-baris">
                                        <x-aksi ikon="check-lg" label="Verifikasi" utama
                                                :action="route('admin.verifikasi.sosmed.setujui', $akunSosmed)"
                                                :konfirmasi="'Verifikasi akun ' . $akunSosmed->platform . ' milik ' . $akunSosmed->influencer->pengguna->nama . '?'" />

                                        {{-- Tolak butuh kolom alasan, jadi form-nya ditulis langsung --}}
                                        {{-- Membuka pop-up alasan penolakan. Alamat form dan nama akun dikirim lewat data-* --}}
                                        <button type="button" class="tombol-aksi tombol-aksi-bahaya" title="Tolak" aria-label="Tolak"
                                                data-bs-toggle="modal" data-bs-target="#modalTolakSosmed"
                                                data-action="{{ route('admin.verifikasi.sosmed.tolak', $akunSosmed) }}"
                                                data-nama="{{ $akunSosmed->platform }} milik {{ $akunSosmed->influencer->pengguna->nama }}">
                                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
                {{-- Pop-up alasan penolakan akun media sosial. Satu pop-up dipakai untuk semua baris. --}}
        <div class="modal fade" id="modalTolakSosmed" tabindex="-1" aria-labelledby="judulTolakSosmed" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="judulTolakSosmed">Tolak akun <span data-nama-akun></span>?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="field">
                            <label for="alasanTolakSosmed" class="field-label">Alasan penolakan</label>
                            <textarea id="alasanTolakSosmed" name="alasan" rows="3" class="input-aura"
                                      placeholder="Contoh: jumlah pengikut tidak sesuai dengan profil"
                                      maxlength="500" required></textarea>
                            <p class="teks-bantuan">Alasan ini akan tampil di halaman Profil influencer.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-aura btn-aura-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-aura btn-aura-primary">Ya, Tolak</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection