{{-- Detail Campaign
    Atas: Informasi Campaign dan Ketentuan Platform (susunan wireframe Gambar 83).
    Bawah: Owner Brand yang punya akses (Tabel 15) dan daftar pendaftar beserta data seleksi
    (subbab 2.1.4: kecocokan, ER, view rate, label perlu diperiksa, estimasi jangkauan).
    Daftar pendaftar dibagi per status (Menunggu, Diterima, Ditolak) dalam bentuk tab. --}}
@extends('layouts.dashboard')

@section('title', $campaign->namaCampaign . ' - Aura Community')
@section('judul', 'Detail Campaign')

@section('isi')
    <div class="kepala-halaman">
        <a href="{{ route('admin.campaign') }}" class="tautan-kembali">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            Kembali ke Campaign
        </a>

        <div class="aksi-kepala">
            <a href="{{ route('admin.campaign.akses', $campaign) }}" class="btn-aura btn-aura-outline">
                <i class="bi bi-shop" aria-hidden="true"></i>
                Akses
            </a>
            <a href="{{ route('admin.campaign.ubah', $campaign) }}" class="btn-aura btn-aura-outline">
                <i class="bi bi-pencil" aria-hidden="true"></i>
                Ubah
            </a>
            <form method="POST" action="{{ route('admin.campaign.hapus', $campaign) }}" data-konfirmasi="Hapus {{ $campaign->namaCampaign }}?">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-aura btn-aura-outline">
                    <i class="bi bi-trash" aria-hidden="true"></i>
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        {{-- ===== INFORMASI CAMPAIGN ===== --}}
        <div class="col-lg-7">
            <h2 class="judul-bagian">Informasi Campaign</h2>
            <dl class="daftar-data">
                <div class="daftar-data-baris"><dt>Nama Campaign</dt><dd>{{ $campaign->namaCampaign }}</dd></div>
                <div class="daftar-data-baris"><dt>Deskripsi</dt><dd><x-teks-ringkas :teks="$campaign->deskripsi" /></dd></div>
                <div class="daftar-data-baris"><dt>Target Audiens</dt><dd>{{ $campaign->targetAudiens }}</dd></div>
                <div class="daftar-data-baris"><dt>Kategori Influencer</dt><dd>{{ $campaign->kategoriInfluencer }}</dd></div>
                <div class="daftar-data-baris"><dt>Kehadiran</dt><dd>{{ $campaign->wajibHadir ? 'Wajib datang langsung ke lokasi' : 'Tidak wajib hadir' }}</dd></div>
                <div class="daftar-data-baris"><dt>Konten</dt><dd>{{ $campaign->kontenSama ? 'Sama untuk semua platform (draft dikirim sekali)' : 'Berbeda per platform' }}</dd></div>
                <div class="daftar-data-baris"><dt>Target Usia Audiens</dt><dd>{{ $campaign->targetUsia ? config('aura.rentangUsia.' . $campaign->targetUsia) : 'Semua usia' }}</dd></div>
                <div class="daftar-data-baris"><dt>Minimal Pengikut</dt><dd>{{ number_format($campaign->minimalPengikut, 0, ',', '.') }}</dd></div>
                <div class="daftar-data-baris"><dt>Periode</dt><dd>{{ $campaign->tanggalMulai->format('d/m/Y') }} - {{ $campaign->tanggalSelesai->format('d/m/Y') }}</dd></div>
                <div class="daftar-data-baris"><dt>Periode Unggah</dt><dd>{{ $campaign->periodeUnggah() ?? '-' }}</dd></div>
                <div class="daftar-data-baris"><dt>Fee per Influencer</dt><dd><x-rupiah :nilai="$campaign->feePerInfluencer" /></dd></div>
                <div class="daftar-data-baris">
                    <dt>Kuota Influencer</dt>
                    <dd>
                        {{ $jumlahDiterima }} diterima dari kuota {{ $campaign->kuotaInfluencer }}
                        @if ($jumlahDiterima >= $campaign->kuotaInfluencer)
                            <span class="badge-status badge-menunggu">Kuota penuh</span>
                        @endif
                    </dd>
                </div>
                <div class="daftar-data-baris">
                    <dt>Estimasi Jangkauan</dt>
                    <dd>
                        {{ $estimasiCampaign !== null ? number_format($estimasiCampaign, 0, ',', '.') . ' tayangan' : 'Belum dapat dihitung' }}
                        <span class="teks-redup">(dari influencer yang diterima)</span>
                    </dd>
                </div>
                <div class="daftar-data-baris">
                    <dt>Status</dt>
                    <dd><span class="badge-status badge-{{ $campaign->statusCampaign }}">{{ config('aura.statusCampaign.' . $campaign->statusCampaign) }}</span></dd>
                </div>
            </dl>
        </div>

        {{-- ===== KETENTUAN PLATFORM DAN AKSES ===== --}}
        <div class="col-lg-5 tumpukan">
            <section>
                <h2 class="judul-bagian">Ketentuan Platform</h2>
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
                            @foreach ($campaign->ketentuanPlatform as $ketentuan)
                                <tr>
                                    <td>{{ $ketentuan->platform }}</td>
                                    <td>{{ config('aura.jenisKonten.' . $ketentuan->jenisKonten) }}</td>
                                    <td>{{ $ketentuan->jumlahPostingan }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section>
                <h2 class="judul-bagian">Owner Brand dengan Akses ({{ $campaign->ownerBrand->count() }})</h2>
                @if ($campaign->ownerBrand->isEmpty())
                    <div class="kosong">
                        Belum ada Owner Brand yang dapat melihat campaign ini.
                        <a class="tautan" href="{{ route('admin.campaign.akses', $campaign) }}">Berikan akses</a>
                    </div>
                @else
                    <ul class="daftar-chip">
                        @foreach ($campaign->ownerBrand as $ownerBrand)
                            <li class="badge-status">{{ $ownerBrand->namaBrand }}</li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>

    {{-- ===== PENDAFTAR ===== --}}
    <section id="pendaftar">
        <h2 class="judul-bagian">Pendaftar ({{ $campaign->pendaftaran->count() }})</h2>
        <p class="teks-bantuan">
            ER, view rate, dan estimasi dihitung dari insight campaign sebelumnya yang telah disetujui.
            Label <strong>Perlu diperiksa</strong> muncul jika view rate kurang dari {{ config('aura.seleksi.ambangViewRate') }}%
            rata-rata akun lain pada platform dan rentang pengikut yang sama.
            Akun yang belum terverifikasi tidak ikut dihitung.
        </p>

        @if ($campaign->pendaftaran->isEmpty())
            <div class="kosong">Belum ada influencer yang mendaftar campaign ini.</div>
        @else
            @php
                $kuotaPenuh = $jumlahDiterima >= $campaign->kuotaInfluencer;
                $platformCampaign = $campaign->ketentuanPlatform->pluck('platform')->unique()->all();

                // Tab per status pendaftaran. Status lain yang ada di config ikut muncul di belakang.
                $daftarStatus = collect(['menunggu', 'diterima', 'ditolak'])
                    ->merge(array_keys(config('aura.statusPendaftaran', [])))
                    ->unique()
                    ->values();
                $tabAktif = $daftarStatus->contains(request('tab')) ? request('tab') : 'menunggu';
                $perStatus = $campaign->pendaftaran->groupBy('statusPendaftaran');
            @endphp

            {{-- Tab status: berpindah tanpa memuat ulang halaman, dan tetap tersimpan di URL (?tab=...) --}}
            <ul class="tab-status">
                @foreach ($daftarStatus as $kode)
                    <li>
                        <a @class(['aktif' => $kode === $tabAktif])
                           href="{{ route('admin.campaign.detail', [$campaign, 'tab' => $kode]) }}"
                           data-tab="{{ $kode }}"
                           @if ($kode === $tabAktif) aria-current="page" @endif>
                            <span class="tab-status-label">{{ config('aura.statusPendaftaran.' . $kode, ucfirst($kode)) }}</span>
                            <span class="badge-status badge-{{ $kode }}">{{ $perStatus->get($kode, collect())->count() }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="tabel-wrap">
                <table class="tabel-aura">
                    <thead>
                        <tr>
                            <th scope="col">Influencer</th>
                            <th scope="col">Akun dan Demografi</th>
                            <th scope="col">Kecocokan</th>
                            <th scope="col">ER</th>
                            <th scope="col">View Rate</th>
                            <th scope="col">Estimasi</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>

                    {{-- Satu tbody per status; yang bukan tab aktif disembunyikan --}}
                    @foreach ($daftarStatus as $kode)
                        <tbody data-tab-status="{{ $kode }}" @if ($kode !== $tabAktif) hidden @endif>
                            @forelse ($perStatus->get($kode, collect()) as $pendaftaran)
                                @php
                                    $influencer = $pendaftaran->influencer;
                                    $baris = $seleksi[$pendaftaran->idPendaftaran];
                                    $cocok = $baris->kecocokan;
                                    $tidakCocok = collect($cocok['rincian'])->reject(fn ($sesuai) => $sesuai)->keys();

                                    // Akun di platform campaign yang belum terverifikasi: ditampilkan, tetapi tidak dihitung
                                    $akunBelumTerverifikasi = $influencer->akunSosmed
                                        ->whereIn('platform', $platformCampaign)
                                        ->reject(fn ($akun) => $akun->terverifikasi());
                                @endphp
                                <tr>
                                    <td>
                                        <a class="tautan" href="{{ route('admin.influencer.detail', $influencer) }}">{{ $baris->nama }}</a>
                                        <div class="teks-redup">{{ $influencer->kategori }} · {{ $pendaftaran->tanggalPendaftaran->format('d/m/Y') }}</div>
                                        @if ($influencer->statusBlacklist)
                                            <span class="badge-status badge-blacklist">Blacklist</span>
                                        @endif
                                        @if ($baris->perluDiperiksa)
                                            <span class="badge-status badge-ditolak">Perlu diperiksa</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{-- Akun terverifikasi: dipakai untuk kecocokan, ER, view rate, dan estimasi --}}
                                        @foreach ($baris->akun as $akun)
                                            <div @class(['mt-2' => ! $loop->first])>
                                                {{ $akun->platform }} {{ number_format($akun->jumlahPengikut, 0, ',', '.') }}
                                                @if (\Illuminate\Support\Str::startsWith($akun->linkProfil, ['https://', 'http://']))
                                                    · <a class="tautan" href="{{ $akun->linkProfil }}" target="_blank" rel="noopener noreferrer">profil</a>
                                                @endif
                                                @if ($akun->fileDemografi)
                                                    · <a class="tautan" href="{{ route('berkas.demografi', $akun) }}" target="_blank" rel="noopener noreferrer">insight audiens</a>
                                                @endif
                                                <div class="teks-redup">
                                                    {{ $akun->domisiliAudiens ?? '-' }}
                                                    · {{ $akun->usiaDominan ? config('aura.rentangUsia.' . $akun->usiaDominan) : '-' }}
                                                    · {{ $akun->genderDominan ? config('aura.genderAudiens.' . $akun->genderDominan) : '-' }}
                                                </div>
                                            </div>
                                        @endforeach

                                        {{-- Akun belum terverifikasi: hanya ditandai, angkanya tidak dihitung --}}
                                        @foreach ($akunBelumTerverifikasi as $akun)
                                            <div @class(['mt-2' => $baris->akun->isNotEmpty() || ! $loop->first])>
                                                <span class="teks-redup">{{ $akun->platform }} {{ number_format($akun->jumlahPengikut, 0, ',', '.') }}</span>
                                                @if ($akun->statusVerifikasi === 'menunggu')
                                                    <span class="badge-status badge-menunggu">Menunggu verifikasi</span>
                                                    <a class="tautan" href="{{ route('admin.verifikasi') }}">Periksa</a>
                                                @else
                                                    <span class="badge-status badge-ditolak">Ditolak</span>
                                                @endif
                                            </div>
                                        @endforeach

                                        @if ($baris->akun->isEmpty() && $akunBelumTerverifikasi->isEmpty())
                                            <span class="teks-redup">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span @class([
                                            'badge-status',
                                            'badge-diterima' => $cocok['skor'] === $cocok['maks'],
                                            'badge-menunggu' => $cocok['skor'] < $cocok['maks'] && $cocok['skor'] >= 2,
                                            'badge-ditolak' => $cocok['skor'] < 2,
                                        ])>{{ $cocok['skor'] }}/{{ $cocok['maks'] }}</span>
                                        @if ($tidakCocok->isNotEmpty())
                                            <div class="teks-redup">Tidak sesuai: {{ $tidakCocok->join(', ') }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $baris->er !== null ? number_format($baris->er, 2, ',', '.') . '%' : '-' }}</td>
                                    <td>{{ $baris->viewRate !== null ? number_format($baris->viewRate, 2, ',', '.') . '%' : '-' }}</td>
                                    <td>
                                        @if ($baris->estimasi !== null)
                                            {{ number_format($baris->estimasi, 0, ',', '.') }}
                                        @else
                                            <span class="teks-redup">Belum ada riwayat</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge-status badge-{{ $pendaftaran->statusPendaftaran }}">
                                            {{ config('aura.statusPendaftaran.' . $pendaftaran->statusPendaftaran) }}
                                        </span>
                                    </td>
                                    <td class="kolom-aksi">
                                        @if ($pendaftaran->statusPendaftaran === 'menunggu')
                                            <div class="aksi-baris">
                                                {{-- Kuota penuh: tombol Terima diganti keterangan, Tolak tetap ada --}}
                                                @if ($kuotaPenuh)
                                                    <span class="teks-redup">Kuota penuh</span>
                                                @else
                                                    <x-aksi ikon="check-lg" label="Terima" utama
                                                            :action="route('admin.campaign.pendaftar.terima', [$campaign, $pendaftaran])"
                                                            :konfirmasi="'Terima ' . $baris->nama . ' di campaign ini?'" />
                                                @endif
                                                <x-aksi ikon="x-lg" label="Tolak" bahaya
                                                        :action="route('admin.campaign.pendaftar.tolak', [$campaign, $pendaftaran])"
                                                        :konfirmasi="'Tolak pendaftaran ' . $baris->nama . '?'" />
                                            </div>
                                        @else
                                            <span class="teks-redup">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="teks-redup">Tidak ada pendaftar dengan status ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    @endforeach
                </table>
            </div>
        @endif
    </section>

    {{-- Gaya tab status: mengikuti font dan warna teks halaman, bukan gaya bawaan Bootstrap --}}
    <style>
        .tab-status {
            display: flex;
            flex-wrap: wrap;
            gap: .25rem;
            margin: 0 0 1rem;
            padding: 0;
            list-style: none;
            border-bottom: 1px solid rgba(0, 0, 0, .08);
        }
        .tab-status a {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .6rem .9rem;
            margin-bottom: -1px;
            font: inherit;
            font-weight: 500;
            color: inherit;
            text-decoration: none;
            border-bottom: 2px solid transparent;
            transition: border-color .15s ease;
        }
        .tab-status-label {
            opacity: .55;
            transition: opacity .15s ease;
        }
        .tab-status a:hover .tab-status-label {
            opacity: .85;
        }
        .tab-status a.aktif {
            font-weight: 600;
            border-bottom-color: currentColor;
        }
        .tab-status a.aktif .tab-status-label {
            opacity: 1;
        }
        #pendaftar tbody[hidden] {
            display: none;
        }
                .tab-status .badge-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.9em;
            height: 1.9em;
            padding: 0;
            border-radius: 50%;
            font-size: .8em;
            line-height: 1;
            flex-shrink: 0;
        }
    </style>

    <script>
        (function () {
            var kunci = 'aura-scroll:' + location.pathname;
            var akar = document.documentElement;

            // Gulir langsung tanpa animasi (mengabaikan scroll-behavior: smooth dari CSS)
            function gulirKe(y) {
                var semula = akar.style.scrollBehavior;
                akar.style.scrollBehavior = 'auto';
                window.scrollTo(0, y);
                akar.style.scrollBehavior = semula;
            }

            // 1. Setelah Terima/Tolak: kembalikan posisi layar seperti sebelum klik
            var simpan = null;
            try {
                simpan = JSON.parse(sessionStorage.getItem(kunci) || 'null');
                sessionStorage.removeItem(kunci);
            } catch (e) {}

            if (simpan && Date.now() - simpan.waktu < 60000) {
                gulirKe(simpan.y);
                window.addEventListener('load', function () { gulirKe(simpan.y); });
            }

            var bagian = document.getElementById('pendaftar');
            if (!bagian) return;

            // Ingat posisi layar saat form Terima/Tolak dikirim
            bagian.addEventListener('submit', function () {
                try {
                    sessionStorage.setItem(kunci, JSON.stringify({ y: window.scrollY, waktu: Date.now() }));
                } catch (e) {}
            }, true);

            // 2. Pindah tab tanpa memuat ulang halaman
            var semuaTab = bagian.querySelectorAll('.tab-status a[data-tab]');

            semuaTab.forEach(function (tautan) {
                tautan.addEventListener('click', function (e) {
                    // Ctrl/Cmd + klik tetap membuka tab browser baru seperti biasa
                    if (e.ctrlKey || e.metaKey || e.shiftKey) return;
                    e.preventDefault();

                    var kode = tautan.dataset.tab;

                    semuaTab.forEach(function (t) {
                        var aktif = t === tautan;
                        t.classList.toggle('aktif', aktif);
                        if (aktif) {
                            t.setAttribute('aria-current', 'page');
                        } else {
                            t.removeAttribute('aria-current');
                        }
                    });

                    bagian.querySelectorAll('tbody[data-tab-status]').forEach(function (isi) {
                        isi.hidden = isi.dataset.tabStatus !== kode;
                    });

                    // Simpan tab di URL tanpa memuat ulang, supaya refresh tetap di tab ini
                    var url = new URL(location.href);
                    url.searchParams.set('tab', kode);
                    history.replaceState(null, '', url);
                });
            });
        })();
    </script>
@endsection