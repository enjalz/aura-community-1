{{-- Verifikasi Akun.
     Atas kiri: daftar akun influencer baru. Atas kanan: data influencer yang sedang diperiksa,
     termasuk demografi audiens dan screenshot insight untuk dicocokkan (Tabel 8 langkah 5).
     Bawah: akun media sosial yang ditambah atau diubah influencer setelah akunnya terverifikasi.
     Pesan sukses/gagal setelah Verifikasi/Tolak ditampilkan oleh layouts/dashboard.blade.php. --}}
@extends('layouts.dashboard')

@section('title', 'Verifikasi Akun - Aura Community')
@section('judul', 'Verifikasi Akun')

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
                                        <a class="tautan" href="{{ request()->fullUrlWithQuery(['pilih' => $influencer->idPengguna]) }}">Periksa</a>
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
                                        <form method="POST" action="{{ route('admin.verifikasi.sosmed.setujui', $akunSosmed) }}" class="form-sebaris"
                                              data-konfirmasi="Verifikasi akun {{ $akunSosmed->platform }} milik {{ $akunSosmed->influencer->pengguna->nama }}?">
                                            @csrf
                                            <button type="submit" class="btn-aura btn-aura-primary btn-aura-sm">Verifikasi</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.verifikasi.sosmed.tolak', $akunSosmed) }}" class="form-sebaris"
                                              data-konfirmasi="Tolak akun {{ $akunSosmed->platform }} milik {{ $akunSosmed->influencer->pengguna->nama }}?">
                                            @csrf
                                            <input type="text" name="alasan" class="input-aura" placeholder="Alasan penolakan"
                                                   style="width: 200px; height: 36px;" aria-label="Alasan penolakan">
                                            <button type="submit" class="btn-aura btn-aura-outline btn-aura-sm">Tolak</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection