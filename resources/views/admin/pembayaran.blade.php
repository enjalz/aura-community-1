{{-- Pembayaran (wireframe Gambar 75, Tabel 24).
     Atas: daftar pendaftaran yang siap dibayar atau sudah punya data pembayaran.
     Bawah: Informasi Campaign, Rekening Influencer, dan tombol Proses Pembayaran. --}}
@extends('layouts.dashboard')

@section('title', 'Pembayaran - Aura Community')
@section('judul', 'Pembayaran')

@push('scripts')
    <script src="{{ asset('js/filter.js') }}"></script>
@endpush

@section('isi')
    <form method="GET" action="{{ route('admin.pembayaran') }}" class="toolbar" data-filter-otomatis>
        <div class="input-cari">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="cari" value="{{ $filter['cari'] }}" class="input-aura"
                   placeholder="Cari nama influencer" aria-label="Cari influencer">
        </div>
        <select name="campaign" class="input-aura input-pilih" aria-label="Filter campaign">
            <option value="">Semua Campaign</option>
            @foreach ($pilihanCampaign as $id => $nama)
                <option value="{{ $id }}" @selected($filter['campaign'] === $id)>{{ $nama }}</option>
            @endforeach
        </select>
        <select name="status" class="input-aura input-pilih" aria-label="Filter status pembayaran">
            <option value="">Semua Status</option>
            @foreach (config('aura.statusPembayaran') as $nilai => $label)
                <option value="{{ $nilai }}" @selected($filter['status'] === $nilai)>{{ $label }}</option>
            @endforeach
        </select>
    </form>

    @if ($daftar->isEmpty())
        <div class="kosong">
            {{ array_filter($filter) ? 'Tidak ada data yang cocok dengan pencarian atau filter.' : 'Belum ada pengerjaan yang siap dibayar. Pembayaran tersedia setelah seluruh hasil pengerjaan dan insight disetujui.' }}
        </div>
    @else
        <div class="tabel-wrap">
            <table class="tabel-aura">
                <thead>
                    <tr>
                        <th scope="col">Influencer</th>
                        <th scope="col">Campaign</th>
                        <th scope="col">Jumlah</th>
                        <th scope="col">Tanggal Bayar</th>
                        <th scope="col">ID Transaksi</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="kolom-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                     @foreach ($daftar as $pendaftaran)
                        @php
                            $pembayaran = $pendaftaran->pembayaran;
                        @endphp
                        <tr @class(['is-terpilih' => $terpilih?->is($pendaftaran)])>
                            <td>{{ $pendaftaran->influencer->pengguna->nama }}</td>
                            <td>{{ $pendaftaran->campaign->namaCampaign }}</td>
                            <td><x-rupiah :nilai="$pembayaran?->jumlahPembayaran ?? $pendaftaran->campaign->feePerInfluencer" /></td>
                            <td>{{ $pembayaran?->tanggalPembayaran->format('d/m/Y') ?? '-' }}</td>
                            <td><span class="teks-kode">{{ $pembayaran?->idTransaksiGateway ?? '-' }}</span></td>
                            <td><x-status jenis="statusPembayaran" :nilai="$pembayaran?->statusPembayaran ?? 'belum'" /></td>
                            <td class="kolom-aksi">
                                <a class="tautan" href="{{ request()->fullUrlWithQuery(['pilih' => $pendaftaran->idPendaftaran]) }}">Pilih</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $daftar->links('partials.paginasi', ['label' => 'data']) }}
    @endif

    {{-- ===== INFORMASI PEMBAYARAN ===== --}}
    @if ($terpilih)
        @php
            $pembayaran = $terpilih->pembayaran;
            $rekening = $terpilih->influencer->rekening;
            $status = $pembayaran?->statusPembayaran ?? 'belum';
        @endphp

        <section>
            <h2 class="judul-bagian">Informasi Pembayaran</h2>

            <div class="panel-detail">
                <div class="panel-detail-utama">
                    <p class="label-grup">Informasi Campaign</p>
                    <dl class="info-daftar">
                        <div><dt>Influencer</dt><dd>{{ $terpilih->influencer->pengguna->nama }}</dd></div>
                        <div><dt>Campaign</dt><dd>{{ $terpilih->campaign->namaCampaign }}</dd></div>
                        <div><dt>Hasil Pengerjaan</dt><dd>{{ $terpilih->jumlahDisetujui }} dari {{ $terpilih->jumlahPlatform }} disetujui final</dd></div>
                        <div><dt>Status</dt><dd><x-status jenis="statusPembayaran" :nilai="$status" /></dd></div>
                        @if ($pembayaran?->idTransaksiGateway)
                            <div><dt>ID Transaksi</dt><dd class="teks-kode">{{ $pembayaran->idTransaksiGateway }}</dd></div>
                        @endif
                        @if ($pembayaran?->waktuKonfirmasi)
                            <div><dt>Waktu Konfirmasi</dt><dd>{{ $pembayaran->waktuKonfirmasi->format('d/m/Y H:i') }}</dd></div>
                        @endif
                    </dl>
                </div>

                <div class="garis-tegak" aria-hidden="true"></div>

                {{-- Tabel 24 langkah 2: rincian rekening influencer --}}
                <div class="panel-detail-utama">
                    <p class="label-grup">Rekening Influencer</p>
                    @if ($rekening)
                        <dl class="info-daftar">
                            <div><dt>Jenis Rekening</dt><dd>{{ $rekening->jenisRekening === 'bank' ? 'Bank' : 'E-Wallet' }}</dd></div>
                            <div><dt>Nama Penyedia</dt><dd>{{ $rekening->namaPenyedia }}</dd></div>
                            <div><dt>Nomor Rekening</dt><dd>{{ $rekening->nomorRekening }}</dd></div>
                            <div><dt>Nama Pemilik</dt><dd>{{ $rekening->namaPemilik }}</dd></div>
                        </dl>
                    @else
                        <div class="catatan">Influencer belum mengisi data rekening.</div>
                    @endif
                </div>

                <div class="garis-tegak" aria-hidden="true"></div>

                <div class="panel-detail-samping kolom-bayar">
                    <div class="kotak-total">
                        <span>Jumlah Pembayaran</span>
                        <strong><x-rupiah :nilai="$pembayaran?->jumlahPembayaran ?? $terpilih->campaign->feePerInfluencer" /></strong>
                    </div>

                    @if (in_array($status, ['belum', 'gagal'], true))
                        {{-- Tabel 24 langkah 3-4: Admin memeriksa lalu memproses --}}
                        <form method="POST" action="{{ route('admin.pembayaran.proses', $terpilih) }}"
                              data-konfirmasi="Kirim fee ke rekening {{ $terpilih->influencer->pengguna->nama }} melalui Xendit?">
                            @csrf
                            <button type="submit" class="btn-aura btn-aura-primary w-100" @disabled(! $rekening)>
                                {{ $status === 'gagal' ? 'Proses Ulang Pembayaran' : 'Proses Pembayaran' }}
                            </button>
                        </form>
                        <p class="teks-bantuan">Dana dikirim melalui Xendit (mode Test).</p>
                    @elseif ($status === 'pending')
                        <form method="POST" action="{{ route('admin.pembayaran.cek-status', $pembayaran) }}">
                            @csrf
                            <button type="submit" class="btn-aura btn-aura-outline w-100">
                                <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
                                Cek Status
                            </button>
                        </form>
                        <p class="teks-bantuan">Pembayaran sedang diproses bank. Cek status untuk melihat hasil akhirnya.</p>
                    @else
                        <div class="catatan">Pembayaran sudah berhasil diterima rekening influencer.</div>
                    @endif
                </div>
            </div>
        </section>
    @endif
@endsection