{{-- Dashboard Influencer (wireframe Gambar 82, Tabel 6) --}}
@extends('layouts.dashboard')

@section('title', 'Dashboard Influencer - Aura Community')
@section('judul', 'Dashboard')

@section('isi')
    <section>
        <h2 class="judul-bagian">Ringkasan</h2>
        <div class="statistik-grid">
            @foreach ($ringkasan as $label => $angka)
                <div class="kartu-angka">
                    <span>{{ $label }}</span>
                    <strong>{{ number_format($angka, 0, ',', '.') }}</strong>
                </div>
            @endforeach
        </div>
    </section>

    <section>
        <h2 class="judul-bagian">Campaign Saya</h2>
        @if ($campaignSaya->isEmpty())
            <div class="kosong">Belum ada campaign yang Anda daftarkan.</div>
        @else
            <div class="tabel-wrap">
                <table class="tabel-aura">
                    <thead>
                        <tr>
                            <th scope="col">Nama Campaign</th>
                            <th scope="col">Status Pendaftaran</th>
                            <th scope="col">Status Draft</th>
                            <th scope="col">Status Insight</th>
                            <th scope="col">Status Pembayaran</th>
                            <th scope="col" class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($campaignSaya as $baris)
                            @php
                                $pendaftaran = $baris->pendaftaran;
                            @endphp
                            <tr>
                                <td>{{ $pendaftaran->campaign->namaCampaign }}</td>
                                <td><x-status jenis="statusPendaftaran" :nilai="$pendaftaran->statusPendaftaran" /></td>
                                <td>
                                    @if ($baris->statusDraft)
                                        <x-status jenis="statusDraft" :nilai="$baris->statusDraft" />
                                    @else
                                        <span class="teks-redup">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($baris->statusInsight)
                                        <x-status jenis="statusInsight" :nilai="$baris->statusInsight" />
                                    @else
                                        <span class="teks-redup">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($pendaftaran->pembayaran)
                                        <x-status jenis="statusPembayaran" :nilai="$pendaftaran->pembayaran->statusPembayaran" />
                                    @else
                                        <span class="teks-redup">-</span>
                                    @endif
                                </td>
                                <td class="kolom-aksi">
                                    {{-- Yang sudah diterima diarahkan ke Hasil Pengerjaan, yang lain ke detail campaign --}}
                                    <a class="tautan" href="{{ $pendaftaran->statusPendaftaran === 'diterima' ? route('influencer.hasil') : route('influencer.campaign', ['pilih' => $pendaftaran->idCampaign]) }}">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section>
        <h2 class="judul-bagian">Feedback Terbaru</h2>
        @if ($feedbackTerbaru->isEmpty())
            <div class="kosong">Belum ada feedback dari Admin.</div>
        @else
            <div class="tabel-wrap">
                <table class="tabel-aura">
                    <thead>
                        <tr>
                            <th scope="col">Nama Campaign</th>
                            <th scope="col">Isi Feedback</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col" class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($feedbackTerbaru as $feedback)
                            <tr>
                                <td>{{ $feedback->hasilPengerjaan->pendaftaran->campaign->namaCampaign }}</td>
                                {{-- Str::limit memotong teks panjang menjadi 80 karakter dengan "..." di akhir --}}
                                <td class="sel-teks">{{ \Illuminate\Support\Str::limit($feedback->isiFeedback, 80) }}</td>
                                <td>{{ $feedback->tanggalFeedback->format('d/m/Y') }}</td>
                                <td class="kolom-aksi"><a class="tautan" href="{{ route('influencer.hasil') }}">Lihat</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section>
        <h2 class="judul-bagian">Campaign Tersedia</h2>
        @if ($campaignTersedia->isEmpty())
            <div class="kosong">Belum ada campaign baru untuk kategori Anda.</div>
        @else
            <div class="tabel-wrap">
                <table class="tabel-aura">
                    <thead>
                        <tr>
                            <th scope="col">Nama Campaign</th>
                            <th scope="col">Kategori Influencer</th>
                            <th scope="col">Fee per Influencer</th>
                            <th scope="col">Kuota Influencer</th>
                            <th scope="col">Tanggal Selesai</th>
                            <th scope="col" class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($campaignTersedia as $campaign)
                            <tr>
                                <td>{{ $campaign->namaCampaign }}</td>
                                <td>{{ $campaign->kategoriInfluencer }}</td>
                                <td><x-rupiah :nilai="$campaign->feePerInfluencer" /></td>
                                <td>{{ $campaign->kuotaInfluencer }}</td>
                                <td>{{ $campaign->tanggalSelesai->format('d/m/Y') }}</td>
                                <td class="kolom-aksi"><a class="tautan" href="{{ route('influencer.campaign', ['pilih' => $campaign->idCampaign]) }}">Detail</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection