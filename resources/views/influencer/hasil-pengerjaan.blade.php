{{-- Hasil Pengerjaan Influencer (wireframe Gambar 84, Tabel 18 dan 22).
     Atas: campaign yang diikuti, satu baris per platform.
     Bawah: 1. Draft Konten (kiri) dan 2. Link dan Insight (kanan). --}}
@extends('layouts.dashboard')

@section('title', 'Hasil Pengerjaan - Aura Community')
@section('judul', 'Hasil Pengerjaan')

@section('isi')
    <section>
        <h2 class="judul-bagian">Campaign yang Diikuti</h2>

        @if ($daftar->isEmpty())
            <div class="kosong">
                Belum ada campaign yang diikuti. Daftar campaign di menu
                <a class="tautan" href="{{ route('influencer.campaign') }}">Campaign</a>, lalu tunggu Admin menerima pendaftaran Anda.
            </div>
        @else
            <div class="tabel-wrap">
                <table class="tabel-aura">
                    <thead>
                        <tr>
                            <th scope="col">Nama Campaign</th>
                            <th scope="col">Akun Media Sosial</th>
                            <th scope="col">Status Draft</th>
                            <th scope="col">Status Insight</th>
                            <th scope="col" class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($daftar as $baris)
                            <tr @class(['is-terpilih' => $terpilih?->kunci === $baris->kunci])>
                                <td>{{ $baris->pendaftaran->campaign->namaCampaign }}</td>
                                <td>{{ $baris->platform }} - {{ $baris->akun ? '@' . $baris->akun->username : 'Belum ada akun' }}</td>
                                <td>
                                    @if ($baris->hasil)
                                        <x-status jenis="statusDraft" :nilai="$baris->hasil->statusDraft" />
                                    @else
                                        <span class="teks-redup">Belum dikirim</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($baris->hasil)
                                        <x-status jenis="statusInsight" :nilai="$baris->hasil->statusInsight" />
                                    @else
                                        <span class="teks-redup">-</span>
                                    @endif
                                </td>
                                <td class="kolom-aksi">
                                    <a class="tautan" href="{{ route('influencer.hasil', ['pilih' => $baris->kunci]) }}">Pilih</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    @if ($terpilih)
        @php($hasil = $terpilih->hasil)

        <section>
            <h2 class="judul-bagian">{{ $terpilih->pendaftaran->campaign->namaCampaign }} - {{ $terpilih->platform }}</h2>

            <div class="panel-detail">
                {{-- ===== 1. DRAFT KONTEN (Tabel 18) ===== --}}
                <div class="panel-detail-utama">
                    <div class="kepala-langkah">
                        <p class="label-grup">1. Draft Konten</p>
                        @if ($hasil)
                            <x-status jenis="statusDraft" :nilai="$hasil->statusDraft" />
                        @endif
                    </div>

                    @if ($hasil)
                        <div class="field">
                            <span class="field-label">Berkas Draft</span>
                            <div class="kotak-berkas">
                                <span>Draft konten ({{ strtoupper(pathinfo($hasil->draftKonten, PATHINFO_EXTENSION)) }})</span>
                                <a class="tautan" href="{{ route('berkas.hasil', [$hasil, 'draft']) }}" target="_blank" rel="noopener">Lihat</a>
                            </div>
                        </div>
                        <div class="field">
                            <span class="field-label">Tanggal Kirim Draft</span>
                            <div class="kotak-berkas">{{ $hasil->tanggalPengirimanDraft->format('d/m/Y') }}</div>
                        </div>

                        {{-- Catatan dari Admin, terbaru di atas (Tabel 20 langkah 4) --}}
                        @if ($hasil->feedback->isNotEmpty())
                            <div class="field">
                                <span class="field-label">Feedback Admin</span>
                                <ul class="daftar-feedback">
                                    @foreach ($hasil->feedback as $feedback)
                                        <li>
                                            <p class="teks-panjang">{{ $feedback->isiFeedback }}</p>
                                            <small>{{ $feedback->tanggalFeedback->format('d/m/Y') }}</small>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endif

                    @if (! $terpilih->akun)
                        <div class="catatan">Anda belum memiliki akun {{ $terpilih->platform }}. Tambahkan akun di menu Profil untuk mengirim draft platform ini.</div>
                    @elseif (! $hasil || $hasil->statusDraft === 'revisi')
                        {{-- Form kirim draft: pertama kali, atau kirim ulang setelah revisi.
                             enctype multipart/form-data wajib supaya berkas ikut terkirim. --}}
                        <form method="POST" action="{{ route('influencer.hasil.draft', $terpilih->pendaftaran) }}"
                              enctype="multipart/form-data" class="tumpukan-kecil"
                              data-konfirmasi="Kirim draft ini ke Admin?">
                            @csrf
                            <input type="hidden" name="platform" value="{{ $terpilih->platform }}">

                            <div class="field">
                                <label for="draftKonten" class="field-label">{{ $hasil ? 'Unggah Draft Revisi' : 'Unggah Berkas Draft' }}</label>
                                <input type="file" id="draftKonten" name="draftKonten" accept=".mp4,.mov,.webm,.jpg,.jpeg,.png"
                                       @class(['input-aura', 'input-berkas', 'is-invalid' => $errors->has('draftKonten')])>
                                @error('draftKonten')
                                    <p class="pesan-error">{{ $message }}</p>
                                @enderror
                                <p class="teks-bantuan">Video (mp4, mov, webm) atau gambar (jpg, png), maksimal 100 MB.</p>
                            </div>

                            <button type="submit" class="btn-aura btn-aura-primary">{{ $hasil ? 'Kirim Ulang Draft' : 'Kirim Draft' }}</button>
                        </form>
                    @else
                        {{-- Draft sedang diperiksa atau sudah disetujui: tidak ada form maupun tombol,
                             cukup keterangan status supaya influencer tahu langkah berikutnya --}}
                        <div class="catatan">
                            @if ($hasil->statusDraft === 'menunggu')
                                Draft sedang diperiksa Admin. Anda akan bisa mengirim ulang kalau Admin meminta revisi.
                            @else
                                Draft sudah disetujui. Unggah konten ke akun {{ $terpilih->platform }} Anda, lalu kirim link dan insight di bagian 2.
                            @endif
                        </div>
                    @endif
                </div>

                <div class="garis-tegak" aria-hidden="true"></div>

                {{-- ===== 2. LINK DAN INSIGHT (Tabel 22) ===== --}}
                <div class="panel-detail-utama">
                    <div class="kepala-langkah">
                        <p class="label-grup">2. Link dan Insight</p>
                        @if ($hasil)
                            <x-status jenis="statusInsight" :nilai="$hasil->statusInsight" />
                        @endif
                    </div>

                    @if (! $hasil || $hasil->statusDraft !== 'disetujui')
                        <div class="catatan">Form insight terbuka setelah draft disetujui Admin.</div>
                    @elseif (in_array($hasil->statusInsight, ['belum_dikirim', 'ditolak'], true))
                        <form method="POST" action="{{ route('influencer.hasil.insight', $hasil) }}"
                              enctype="multipart/form-data" class="tumpukan-kecil"
                              data-konfirmasi="Kirim insight ini ke Admin?" novalidate>
                            @csrf

                            <x-input name="linkKonten" label="Link Konten yang Sudah Tayang" type="url"
                                     :value="$hasil->linkKonten" placeholder="Tempel tautan konten" />

                            <div class="field">
                                <label for="fileInsight" class="field-label">Tangkapan Layar Insight</label>
                                <input type="file" id="fileInsight" name="fileInsight" accept=".jpg,.jpeg,.png,.webp"
                                       @class(['input-aura', 'input-berkas', 'is-invalid' => $errors->has('fileInsight')])>
                                @error('fileInsight')
                                    <p class="pesan-error">{{ $message }}</p>
                                @enderror
                                <p class="teks-bantuan">Gambar jpg, png, atau webp, maksimal 5 MB.</p>
                            </div>

                            <div class="row g-3">
                                <div class="col-4"><x-input name="jumlahViews" label="Jumlah Views" type="number" min="0" :value="$hasil->jumlahViews" placeholder="0" /></div>
                                <div class="col-4"><x-input name="jumlahLikes" label="Jumlah Likes" type="number" min="0" :value="$hasil->jumlahLikes" placeholder="0" /></div>
                                <div class="col-4"><x-input name="jumlahKomentar" label="Jumlah Komentar" type="number" min="0" :value="$hasil->jumlahKomentar" placeholder="0" /></div>
                                <div class="col-6"><x-input name="jumlahPengikut" label="Jumlah Pengikut Saat Ini" type="number" min="1" :value="$hasil->jumlahPengikut ?? $terpilih->akun?->jumlahPengikut" placeholder="0" /></div>
                            </div>

                            <p class="teks-bantuan">Isi jumlah pengikut akun {{ $terpilih->platform }} sesuai kondisi saat insight dikirim.</p>

                            <button type="submit" class="btn-aura btn-aura-primary">{{ $hasil->statusInsight === 'ditolak' ? 'Kirim Ulang Insight' : 'Kirim Insight' }}</button>
                        </form>
                    @else
                        {{-- Insight sudah dikirim: tampilkan yang tersimpan --}}
                        <div class="field">
                            <span class="field-label">Link Konten</span>
                            <div class="kotak-berkas">
                                @if (\Illuminate\Support\Str::startsWith($hasil->linkKonten, ['https://', 'http://']))
                                    <a class="tautan" href="{{ $hasil->linkKonten }}" target="_blank" rel="noopener noreferrer">Buka konten yang tayang</a>
                                @endif
                                <a class="tautan" href="{{ route('berkas.hasil', [$hasil, 'insight']) }}" target="_blank" rel="noopener">Lihat tangkapan layar</a>
                            </div>
                        </div>
                        <div class="metrik">
                            <div class="kotak-angka"><span>Views</span><strong>{{ number_format($hasil->jumlahViews, 0, ',', '.') }}</strong></div>
                            <div class="kotak-angka"><span>Likes</span><strong>{{ number_format($hasil->jumlahLikes, 0, ',', '.') }}</strong></div>
                            <div class="kotak-angka"><span>Komentar</span><strong>{{ number_format($hasil->jumlahKomentar, 0, ',', '.') }}</strong></div>
                            <div class="kotak-angka"><span>Pengikut</span><strong>{{ $hasil->jumlahPengikut !== null ? number_format($hasil->jumlahPengikut, 0, ',', '.') : '-' }}</strong></div>
                        </div>
                        <p class="teks-bantuan">
                            {{ $hasil->statusInsight === 'menunggu' ? 'Insight sedang diperiksa Admin.' : 'Insight disetujui. Pembayaran fee akan diproses Admin.' }}
                        </p>
                    @endif
                </div>
            </div>
        </section>
    @endif
@endsection