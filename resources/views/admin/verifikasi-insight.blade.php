{{-- Verifikasi Insight (wireframe Gambar 74, Tabel 23) --}}
@extends('layouts.dashboard')

@section('title', 'Verifikasi Insight - Aura Community')
@section('judul', 'Verifikasi Insight')

@push('scripts')
    <script src="{{ asset('js/filter.js') }}"></script>
@endpush

@php($labelStatus = config('aura.statusInsight.' . $filter['status'], 'Semua Status'))

@section('isi')
    <div class="dua-kolom">
        {{-- ===== KIRI: DAFTAR ===== --}}
        <section class="kolom-utama">
            <form method="GET" action="{{ route('admin.insight') }}" class="toolbar" data-filter-otomatis>
                <select name="campaign" class="input-aura input-pilih flex-fill" aria-label="Filter campaign">
                    <option value="">Semua Campaign</option>
                    @foreach ($pilihanCampaign as $id => $nama)
                        <option value="{{ $id }}" @selected($filter['campaign'] === $id)>{{ $nama }}</option>
                    @endforeach
                </select>
                <select name="status" class="input-aura input-pilih flex-fill" aria-label="Filter status insight">
                    @foreach (['menunggu', 'ditolak', 'disetujui'] as $nilai)
                        <option value="{{ $nilai }}" @selected($filter['status'] === $nilai)>Status: {{ config('aura.statusInsight.' . $nilai) }}</option>
                    @endforeach
                    <option value="semua" @selected($filter['status'] === 'semua')>Semua Status</option>
                </select>
            </form>

            <h2 class="judul-bagian">Insight {{ $labelStatus }} ({{ $daftar->total() }})</h2>

            @if ($daftar->isEmpty())
                <div class="kosong">Tidak ada insight dengan status ini.</div>
            @else
                <div class="tabel-wrap">
                    <table class="tabel-aura">
                        <thead>
                            <tr>
                                <th scope="col">Influencer</th>
                                <th scope="col">Campaign</th>
                                <th scope="col">Tanggal Kirim</th>
                                <th scope="col">Status Insight</th>
                                <th scope="col" class="kolom-aksi">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($daftar as $hasil)
                                <tr @class(['is-terpilih' => $terpilih?->is($hasil)])>
                                    <td>{{ $hasil->pendaftaran->influencer->pengguna->nama }}</td>
                                    <td>{{ $hasil->pendaftaran->campaign->namaCampaign }}</td>
                                    <td>{{ $hasil->tanggalPengirimanInsight?->format('d/m/Y') ?? '-' }}</td>
                                    <td><x-status jenis="statusInsight" :nilai="$hasil->statusInsight" /></td>
                                    <td class="kolom-aksi">
                                        <a class="tautan" href="{{ request()->fullUrlWithQuery(['pilih' => $hasil->idHasil]) }}">Periksa</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $daftar->links('partials.paginasi', ['label' => 'insight']) }}
            @endif
        </section>

        {{-- ===== KANAN: DETAIL INSIGHT ===== --}}
        <aside class="kolom-samping">
            <h2 class="judul-bagian">Detail Insight</h2>

            @if ($terpilih)
                <div class="form-kartu">
                    <dl class="info-daftar">
                        <div><dt>Influencer</dt><dd>{{ $terpilih->pendaftaran->influencer->pengguna->nama }}</dd></div>
                        <div><dt>Campaign</dt><dd>{{ $terpilih->pendaftaran->campaign->namaCampaign }}</dd></div>
                        <div><dt>Akun Media Sosial</dt><dd>{{ $terpilih->akunSosmed->platform }} - &#64;{{ $terpilih->akunSosmed->username }}</dd></div>
                        <div>
                            <dt>Link Konten</dt>
                            <dd>
                                @if (\Illuminate\Support\Str::startsWith($terpilih->linkKonten, ['https://', 'http://']))
                                    <a class="tautan" href="{{ $terpilih->linkKonten }}" target="_blank" rel="noopener noreferrer">Buka konten yang tayang</a>
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div><dt>Tanggal Kirim Insight</dt><dd>{{ $terpilih->tanggalPengirimanInsight?->format('d/m/Y') ?? '-' }}</dd></div>
                    </dl>

                    {{-- Tabel 23 langkah 4-5: tangkapan layar dibandingkan dengan angka yang diisi --}}
                    <x-pratinjau-berkas :hasil="$terpilih" jenis="insight" />

                    <div class="metrik">
                        <div class="kotak-angka"><span>Views</span><strong>{{ number_format((int) $terpilih->jumlahViews, 0, ',', '.') }}</strong></div>
                        <div class="kotak-angka"><span>Likes</span><strong>{{ number_format((int) $terpilih->jumlahLikes, 0, ',', '.') }}</strong></div>
                        <div class="kotak-angka"><span>Komentar</span><strong>{{ number_format((int) $terpilih->jumlahKomentar, 0, ',', '.') }}</strong></div>
                        <div class="kotak-angka"><span>Share</span><strong>{{ $terpilih->jumlahShare !== null ? number_format($terpilih->jumlahShare, 0, ',', '.') : '-' }}</strong></div>
                        <div class="kotak-angka"><span>Save</span><strong>{{ $terpilih->jumlahSave !== null ? number_format($terpilih->jumlahSave, 0, ',', '.') : '-' }}</strong></div>
                        <div class="kotak-angka"><span>Pengikut</span><strong>{{ $terpilih->jumlahPengikut !== null ? number_format($terpilih->jumlahPengikut, 0, ',', '.') : '-' }}</strong></div>
                    </div>

                    @if ($terpilih->statusInsight === 'menunggu')
                        <form method="POST" action="{{ route('admin.insight.tolak', $terpilih) }}" id="form-tolak-insight"
                              data-konfirmasi="Tolak insight ini? Influencer akan diminta mengirim ulang.">
                            @csrf
                            <div class="field">
                                <label for="alasan" class="field-label">Alasan penolakan (diisi jika menolak)</label>
                                <textarea id="alasan" name="alasan" rows="3" placeholder="Contoh: angka views tidak sesuai tangkapan layar"
                                          @class(['input-aura', 'is-invalid' => $errors->has('alasan')])>{{ old('alasan') }}</textarea>
                                @error('alasan')
                                    <p class="pesan-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </form>

                        <div class="row g-2">
                        <div class="col-6">
                            <button type="submit" form="form-tolak-insight" class="btn-aura btn-aura-outline w-100">Tolak</button>
                        </div>
                        <div class="col-6">
                            <form method="POST" action="{{ route('admin.insight.setujui', $terpilih) }}"
                                  data-konfirmasi="Setujui insight ini? Pengerjaan akan siap diproses ke pembayaran.">
                                @csrf
                                <button type="submit" class="btn-aura btn-aura-primary w-100">Setujui Insight</button>
                            </form>
                        </div>
                    </div>
                    @else
                        <p class="teks-bantuan">Insight ini sudah diperiksa dengan status <strong>{{ config('aura.statusInsight.' . $terpilih->statusInsight) }}</strong>.</p>
                    @endif
                </div>
            @else
                <div class="kosong">Pilih insight di daftar untuk memeriksanya.</div>
            @endif
        </aside>
    </div>
@endsection