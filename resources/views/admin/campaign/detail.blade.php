{{-- Detail Campaign (Tabel 14 "melihat").
     Atas: Informasi Campaign dan Ketentuan Platform (susunan wireframe Gambar 83).
     Bawah: Owner Brand yang punya akses (Tabel 15) dan daftar pendaftar (Batasan 7). --}}
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
                <div class="daftar-data-baris"><dt>Deskripsi</dt><dd class="teks-panjang">{{ $campaign->deskripsi }}</dd></div>
                <div class="daftar-data-baris"><dt>Target Audiens</dt><dd>{{ $campaign->targetAudiens }}</dd></div>
                <div class="daftar-data-baris"><dt>Kategori Influencer</dt><dd>{{ $campaign->kategoriInfluencer }}</dd></div>
                <div class="daftar-data-baris"><dt>Lokasi Campaign</dt><dd>{{ $campaign->lokasiCampaign }}</dd></div>
                <div class="daftar-data-baris"><dt>Periode</dt><dd>{{ $campaign->tanggalMulai->format('d/m/Y') }} - {{ $campaign->tanggalSelesai->format('d/m/Y') }}</dd></div>
                <div class="daftar-data-baris"><dt>Fee per Influencer</dt><dd><x-rupiah :nilai="$campaign->feePerInfluencer" /></dd></div>
                <div class="daftar-data-baris"><dt>Kuota Influencer</dt><dd>{{ $jumlahDiterima }} diterima dari kuota {{ $campaign->kuotaInfluencer }}</dd></div>
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
    <section>
        <h2 class="judul-bagian">Pendaftar ({{ $campaign->pendaftaran->count() }})</h2>

        @if ($campaign->pendaftaran->isEmpty())
            <div class="kosong">Belum ada influencer yang mendaftar campaign ini.</div>
        @else
            <div class="tabel-wrap">
                <table class="tabel-aura">
                    <thead>
                        <tr>
                            <th scope="col">Influencer</th>
                            <th scope="col">Kategori</th>
                            <th scope="col">Pengikut</th>
                            <th scope="col">Tanggal Daftar</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($campaign->pendaftaran as $pendaftaran)
                            @php($influencer = $pendaftaran->influencer)
                            <tr>
                                <td>
                                    <a class="tautan" href="{{ route('admin.influencer.detail', $influencer) }}">{{ $influencer->pengguna->nama }}</a>
                                    @if ($influencer->statusBlacklist)
                                        <span class="badge-status badge-blacklist">Blacklist</span>
                                    @endif
                                </td>
                                <td>{{ $influencer->kategori }}</td>
                                <td>
                                    {{-- Contoh: Instagram 45.200 · TikTok 81.000 --}}
                                    {{ $influencer->akunSosmed->map(fn ($akun) => $akun->platform . ' ' . number_format($akun->jumlahPengikut, 0, ',', '.'))->join(' · ') }}
                                </td>
                                <td>{{ $pendaftaran->tanggalPendaftaran->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge-status badge-{{ $pendaftaran->statusPendaftaran }}">
                                        {{ config('aura.statusPendaftaran.' . $pendaftaran->statusPendaftaran) }}
                                    </span>
                                </td>
                                <td class="kolom-aksi">
                                    @if ($pendaftaran->statusPendaftaran === 'menunggu')
                                        <div class="aksi-baris">
                                            <form method="POST" action="{{ route('admin.campaign.pendaftar.terima', [$campaign, $pendaftaran]) }}" class="form-sebaris"
                                                  data-konfirmasi="Terima {{ $influencer->pengguna->nama }} di campaign ini?">
                                                @csrf
                                                <button type="submit" class="btn-aura btn-aura-primary btn-aura-sm">Terima</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.campaign.pendaftar.tolak', [$campaign, $pendaftaran]) }}" class="form-sebaris"
                                                  data-konfirmasi="Tolak pendaftaran {{ $influencer->pengguna->nama }}?">
                                                @csrf
                                                <button type="submit" class="btn-aura btn-aura-outline btn-aura-sm">Tolak</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="teks-redup">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection