{{-- Hasil Campaign (wireframe Gambar 73).
     Tabel 19: memeriksa hasil. Tabel 20: memberi feedback. Tabel 21: menyetujui draft. --}}
@extends('layouts.dashboard')

@section('title', 'Hasil Campaign - Aura Community')
@section('judul', 'Hasil Campaign')

@push('scripts')
    <script src="{{ asset('js/filter.js') }}"></script>
@endpush

@php($labelStatus = config('aura.statusDraft.' . $filter['status'], 'Semua Status'))

@section('isi')
    <div class="dua-kolom">
        {{-- ===== KIRI: DAFTAR ===== --}}
        <section class="kolom-utama">
            <form method="GET" action="{{ route('admin.hasil') }}" class="toolbar" data-filter-otomatis>
                <select name="campaign" class="input-aura input-pilih flex-fill" aria-label="Filter campaign">
                    <option value="">Semua Campaign</option>
                    @foreach ($pilihanCampaign as $id => $nama)
                        <option value="{{ $id }}" @selected($filter['campaign'] === $id)>{{ $nama }}</option>
                    @endforeach
                </select>
                <select name="status" class="input-aura input-pilih flex-fill" aria-label="Filter status draft">
                    @foreach (config('aura.statusDraft') as $nilai => $label)
                        <option value="{{ $nilai }}" @selected($filter['status'] === $nilai)>Status: {{ $label }}</option>
                    @endforeach
                    <option value="semua" @selected($filter['status'] === 'semua')>Semua Status</option>
                </select>
            </form>

            <h2 class="judul-bagian">Hasil Pengerjaan {{ $labelStatus }} ({{ $daftar->total() }})</h2>

            @if ($daftar->isEmpty())
                <div class="kosong">Tidak ada hasil pengerjaan dengan status ini.</div>
            @else
                <div class="tabel-wrap">
                    <table class="tabel-aura">
                        <thead>
                            <tr>
                                <th scope="col">Influencer</th>
                                <th scope="col">Campaign</th>
                                <th scope="col">Tanggal Kirim</th>
                                <th scope="col">Status Draft</th>
                                <th scope="col" class="kolom-aksi">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($daftar as $hasil)
                                <tr @class(['is-terpilih' => $terpilih?->is($hasil)])>
                                    <td>{{ $hasil->pendaftaran->influencer->pengguna->nama }}</td>
                                    <td>{{ $hasil->pendaftaran->campaign->namaCampaign }}</td>
                                    <td>{{ $hasil->tanggalPengirimanDraft->format('d/m/Y') }}</td>
                                    <td><x-status jenis="statusDraft" :nilai="$hasil->statusDraft" /></td>
                                    <td class="kolom-aksi">
                                        <a class="tautan" href="{{ request()->fullUrlWithQuery(['pilih' => $hasil->idHasil]) }}">Periksa</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $daftar->links('partials.paginasi', ['label' => 'hasil pengerjaan']) }}
            @endif
        </section>

        {{-- ===== KANAN: DETAIL HASIL PENGERJAAN ===== --}}
        <aside class="kolom-samping">
            <h2 class="judul-bagian">Detail Hasil Pengerjaan</h2>

            @if ($terpilih)
                <div class="form-kartu">
                    <dl class="info-daftar">
                        <div><dt>Influencer</dt><dd>{{ $terpilih->pendaftaran->influencer->pengguna->nama }}</dd></div>
                        <div><dt>Campaign</dt><dd>{{ $terpilih->pendaftaran->campaign->namaCampaign }}</dd></div>
                        <div><dt>Akun Media Sosial</dt><dd>{{ $terpilih->akunSosmed->platform }} - &#64;{{ $terpilih->akunSosmed->username }}</dd></div>
                        <div><dt>Tanggal Kirim Draft</dt><dd>{{ $terpilih->tanggalPengirimanDraft->format('d/m/Y') }}</dd></div>
                    </dl>

                    {{-- Tabel 19 langkah 4: sistem menampilkan detail konten --}}
                    <x-pratinjau-berkas :hasil="$terpilih" jenis="draft" />

                    @if ($terpilih->feedback->isNotEmpty())
                        <div class="field">
                            <span class="field-label">Riwayat Feedback</span>
                            <ul class="daftar-feedback">
                                @foreach ($terpilih->feedback as $feedback)
                                    <li>
                                        <p class="teks-panjang">{{ $feedback->isiFeedback }}</p>
                                        <small>{{ $feedback->tanggalFeedback->format('d/m/Y') }}</small>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($terpilih->statusDraft === 'menunggu')
                        {{-- Tabel 20: form feedback. Tombol "Kirim Feedback" di bawah memakai atribut form="form-feedback". --}}
                        <form method="POST" action="{{ route('admin.hasil.feedback', $terpilih) }}" id="form-feedback"
                              data-konfirmasi="Kirim feedback dan minta influencer merevisi draft?">
                            @csrf
                            <div class="field">
                                <label for="isiFeedback" class="field-label">Feedback (diisi jika perlu revisi)</label>
                                <textarea id="isiFeedback" name="isiFeedback" rows="4" placeholder="Tulis catatan revisi untuk influencer"
                                          @class(['input-aura', 'is-invalid' => $errors->has('isiFeedback')])>{{ old('isiFeedback') }}</textarea>
                                @error('isiFeedback')
                                    <p class="pesan-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </form>

                        <div class="panel-aksi">
                            <button type="submit" form="form-feedback" class="btn-aura btn-aura-outline w-100">Kirim Feedback</button>

                            {{-- Tabel 21: menyetujui draft --}}
                            <form method="POST" action="{{ route('admin.hasil.setujui', $terpilih) }}"
                                  data-konfirmasi="Setujui draft ini? Influencer akan diminta mengunggah konten.">
                                @csrf
                                <button type="submit" class="btn-aura btn-aura-primary w-100">Setujui Draft</button>
                            </form>
                        </div>
                    @else
                        <p class="teks-bantuan">Draft ini sudah diperiksa dengan status <strong>{{ config('aura.statusDraft.' . $terpilih->statusDraft) }}</strong>.</p>
                    @endif
                </div>
            @else
                <div class="kosong">Pilih hasil pengerjaan di daftar untuk memeriksanya.</div>
            @endif
        </aside>
    </div>
@endsection