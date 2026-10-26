{{-- Campaign untuk Influencer (wireframe Gambar 83, Tabel 16 dan 17).
     Atas: daftar campaign. Bawah: detail campaign yang dipilih dan tombol Daftar Campaign. --}}
@extends('layouts.dashboard')

@section('title', 'Campaign - Aura Community')
@section('judul', 'Campaign')

@push('scripts')
    <script src="{{ asset('js/filter.js') }}"></script>
@endpush

@section('isi')
    <form method="GET" action="{{ route('influencer.campaign') }}" class="toolbar" data-filter-otomatis>
        <div class="input-cari">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="cari" value="{{ $filter['cari'] }}" class="input-aura"
                   placeholder="Cari nama campaign" aria-label="Cari campaign">
        </div>

        <select name="kategori" class="input-aura input-pilih" aria-label="Filter kategori">
            <option value="">Semua Kategori</option>
            @foreach (config('aura.kategori') as $item)
                <option value="{{ $item }}" @selected($filter['kategori'] === $item)>{{ $item }}</option>
            @endforeach
        </select>
    </form>

    @if ($daftar->isEmpty())
        <div class="kosong">
            {{ array_filter($filter) ? 'Tidak ada campaign yang cocok dengan pencarian atau filter.' : 'Belum ada campaign yang sedang berjalan.' }}
        </div>
    @else
        <div class="tabel-wrap">
            <table class="tabel-aura">
                <thead>
                    <tr>
                        <th scope="col">Nama Campaign</th>
                        <th scope="col">Kategori Influencer</th>
                        <th scope="col">Fee per Influencer</th>
                        <th scope="col">Kuota</th>
                        <th scope="col">Tanggal Selesai</th>
                        <th scope="col">Status Pendaftaran</th>
                        <th scope="col" class="kolom-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($daftar as $campaign)
                        @php($pendaftaran = $pendaftaranSaya->get($campaign->idCampaign))
                        <tr @class(['is-terpilih' => $terpilih?->is($campaign)])>
                            <td>{{ $campaign->namaCampaign }}</td>
                            <td>{{ $campaign->kategoriInfluencer }}</td>
                            <td><x-rupiah :nilai="$campaign->feePerInfluencer" /></td>
                            <td>{{ $campaign->kuotaInfluencer }}</td>
                            <td>{{ $campaign->tanggalSelesai->format('d/m/Y') }}</td>
                            <td>
                                @if ($pendaftaran)
                                    <span class="badge-status badge-{{ $pendaftaran->statusPendaftaran }}">
                                        {{ config('aura.statusPendaftaran.' . $pendaftaran->statusPendaftaran) }}
                                    </span>
                                @else
                                    <span class="teks-redup">Belum mendaftar</span>
                                @endif
                            </td>
                            <td class="kolom-aksi">
                                <a class="tautan" href="{{ request()->fullUrlWithQuery(['pilih' => $campaign->idCampaign]) }}">Pilih</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $daftar->links('partials.paginasi', ['label' => 'campaign']) }}
    @endif

    {{-- ===== DETAIL CAMPAIGN ===== --}}
    @if ($terpilih)
        <section>
            <h2 class="judul-bagian">Detail Campaign</h2>

            <div class="panel-detail">
                {{-- Kiri: informasi campaign --}}
                <div class="panel-detail-utama">
                    <p class="label-grup">Informasi Campaign</p>
                    <dl class="info-daftar">
                        <div><dt>Nama Campaign</dt><dd>{{ $terpilih->namaCampaign }}</dd></div>
                        <div><dt>Deskripsi</dt><dd class="teks-panjang">{{ $terpilih->deskripsi }}</dd></div>
                        <div><dt>Target Audiens</dt><dd>{{ $terpilih->targetAudiens }}</dd></div>
                        <div><dt>Kategori Influencer</dt><dd>{{ $terpilih->kategoriInfluencer }}</dd></div>
                        <div><dt>Lokasi Campaign</dt><dd>{{ $terpilih->lokasiCampaign }}</dd></div>
                        <div><dt>Periode</dt><dd>{{ $terpilih->tanggalMulai->format('d/m/Y') }} - {{ $terpilih->tanggalSelesai->format('d/m/Y') }}</dd></div>
                        <div><dt>Periode Unggah Konten</dt><dd>{{ $terpilih->periodeUnggah() ?? '-' }}</dd></div>
                        <div><dt>Fee per Influencer</dt><dd><x-rupiah :nilai="$terpilih->feePerInfluencer" /></dd></div>
                        <div><dt>Kuota Influencer</dt><dd>{{ $terpilih->kuotaInfluencer }}</dd></div>
                    </dl>
                </div>

                <div class="garis-tegak" aria-hidden="true"></div>

                {{-- Kanan: ketentuan platform dan tombol daftar --}}
                <div class="panel-detail-samping">
                    <p class="label-grup">Ketentuan Platform</p>
                    <div class="tabel-wrap">
                        <table class="tabel-aura">
                            <thead>
                                <tr>
                                    <th scope="col">Platform</th>
                                    <th scope="col">Jenis Konten</th>
                                    <th scope="col">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($terpilih->ketentuanPlatform as $ketentuan)
                                    <tr>
                                        <td>{{ $ketentuan->platform }}</td>
                                        <td>{{ config('aura.jenisKonten.' . $ketentuan->jenisKonten) }}</td>
                                        <td>{{ $ketentuan->jumlahPostingan }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($pendaftaranTerpilih)
                        {{-- Sudah mendaftar: tampilkan statusnya --}}
                        <div class="kotak-angka">
                            <span>Status pendaftaran</span>
                            <span class="badge-status badge-{{ $pendaftaranTerpilih->statusPendaftaran }}">
                                {{ config('aura.statusPendaftaran.' . $pendaftaranTerpilih->statusPendaftaran) }}
                            </span>
                        </div>

                        @if ($pendaftaranTerpilih->statusPendaftaran === 'menunggu')
                            <p class="teks-bantuan">Pendaftaran Anda sedang ditinjau Admin.</p>
                        @elseif ($pendaftaranTerpilih->statusPendaftaran === 'diterima')
                            <p class="teks-bantuan">
                                Anda diterima di campaign ini. Kirim draft konten melalui menu Hasil Pengerjaan.
                                @if ($terpilih->periodeUnggah())
                                    Konten wajib diunggah pada <strong>{{ $terpilih->periodeUnggah() }}</strong>,
                                    lalu kirim link dan insight paling lambat <strong>{{ $terpilih->tanggalSelesai->format('d/m/Y') }}</strong>.
                                    Influencer yang tidak mengirim hingga campaign berakhir akan masuk blacklist secara otomatis.
                                @endif
                            </p>
                        @else
                            <p class="teks-bantuan">Pendaftaran Anda tidak diterima untuk campaign ini.</p>
                        @endif

                    @elseif ($alasanTidakBisaDaftar)
                        {{-- Tidak memenuhi kriteria: tombol Daftar tidak ditampilkan,
                             diganti kotak keterangan supaya influencer langsung tahu alasannya --}}
                        <div class="catatan">
                            <strong><i class="bi bi-info-circle" aria-hidden="true"></i> Belum dapat mendaftar</strong>
                            <p class="mb-0">{{ $alasanTidakBisaDaftar }}</p>

                            {{-- Kalau alasannya soal akun media sosial, beri tautan langsung ke halaman Profil --}}
                            @if (\Illuminate\Support\Str::contains($alasanTidakBisaDaftar, 'menu Profil'))
                                <a class="tautan" href="{{ route('influencer.profil') }}">Buka Profil</a>
                            @endif
                        </div>

                    @else
                        {{-- Memenuhi semua kriteria: tampilkan form pendaftaran --}}
                        <form method="POST" action="{{ route('influencer.campaign.daftar', $terpilih) }}" class="tumpukan-kecil"
                              data-konfirmasi="Daftar ke {{ $terpilih->namaCampaign }}?" novalidate>
                            @csrf

                            @if ($influencer->rekening)
                                {{-- Rekening sudah ada: dipakai untuk pembayaran fee campaign ini --}}
                                <p class="teks-bantuan">
                                    Fee akan dibayarkan ke rekening
                                    <strong>{{ $influencer->rekening->namaPenyedia }} {{ $influencer->rekening->nomorRekening }}</strong>
                                    a.n. {{ $influencer->rekening->namaPemilik }}.
                                </p>
                            @else
                                {{-- Rekening belum ada: wajib diisi (catatan wireframe Gambar 83) --}}
                                <p class="label-grup">Rekening Pembayaran Fee</p>

                                <div class="field">
                                    <label for="jenisRekening" class="field-label">Jenis Rekening</label>
                                    <select id="jenisRekening" name="jenisRekening" @class(['input-aura', 'is-invalid' => $errors->has('jenisRekening')])>
                                        <option value="bank" @selected(old('jenisRekening') === 'bank')>Bank</option>
                                        <option value="ewallet" @selected(old('jenisRekening') === 'ewallet')>E-Wallet</option>
                                    </select>
                                    @error('jenisRekening')
                                        <p class="pesan-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Pilihan dari config/aura.php 'kanalPembayaran', supaya kode kanal Xendit bisa ditentukan --}}
                                <div class="field">
                                    <label for="namaPenyedia" class="field-label">Nama Penyedia</label>
                                    <select id="namaPenyedia" name="namaPenyedia" @class(['input-aura', 'is-invalid' => $errors->has('namaPenyedia')])>
                                        <option value="" disabled @selected(! old('namaPenyedia'))>Pilih bank atau e-wallet</option>
                                        @foreach (config('aura.kanalPembayaran') as $kelompok => $daftarPenyedia)
                                            <optgroup label="{{ $kelompok }}">
                                                @foreach (array_keys($daftarPenyedia) as $penyedia)
                                                    <option value="{{ $penyedia }}" @selected(old('namaPenyedia') === $penyedia)>{{ $penyedia }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    @error('namaPenyedia')
                                        <p class="pesan-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <x-input name="nomorRekening" label="Nomor Rekening" inputmode="numeric" placeholder="Hanya angka" />
                                <x-input name="namaPemilik" label="Nama Pemilik" :value="auth()->user()->nama" />
                            @endif

                            @if ($terpilih->periodeUnggah())
                                <p class="teks-bantuan">
                                    Jika diterima, konten wajib diunggah pada <strong>{{ $terpilih->periodeUnggah() }}</strong>.
                                </p>
                            @endif

                            <button type="submit" class="btn-aura btn-aura-primary">Daftar Campaign</button>
                            <p class="teks-bantuan">Informasi rekening diisi saat mendaftar campaign.</p>
                        </form>
                    @endif
                </div>
            </div>
        </section>
    @endif
@endsection