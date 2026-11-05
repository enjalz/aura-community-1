{{-- Hasil Pengerjaan Influencer (wireframe Gambar 84, Tabel 18 dan 22).
     Tanpa ?pilih: daftar campaign yang diikuti, satu baris per campaign dengan status setiap platform.
     Dengan ?pilih: detail campaign, tab per platform berisi 1. Draft Konten dan 2. Link dan Insight.
     Alur per platform: kirim draft, draft disetujui, kirim link (draft dihapus), tunggu 7 hari, kirim insight. --}}
@extends('layouts.dashboard')

@section('title', 'Hasil Pengerjaan - Aura Community')
@section('judul', 'Hasil Pengerjaan')

@push('scripts')
    <script src="{{ asset('js/ukuran-berkas.js') }}"></script>
    <script>
        // Saat tab platform dipindah, alamat halaman ikut diperbarui (?platform=...),
        // supaya setelah refresh atau kirim form yang gagal, tab yang sama tetap terbuka.
        document.querySelectorAll('[data-tab-platform]').forEach(function (tombol) {
            tombol.addEventListener('shown.bs.tab', function () {
                const url = new URL(window.location.href);
                url.searchParams.set('platform', tombol.dataset.tabPlatform);
                history.replaceState(null, '', url);
            });
        });
    </script>
@endpush

@section('isi')
    @include('partials.peringatan-blacklist')

    {{-- ===== DAFTAR CAMPAIGN (hanya tampil kalau belum ada yang dipilih) ===== --}}
    @unless ($terpilih)
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
                                <th scope="col">Periode Unggah</th>
                                <th scope="col">Platform dan Status</th>
                                <th scope="col" class="kolom-aksi">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($daftar as $baris)
                                <tr>
                                    <td>
                                        {{ $baris->campaign->namaCampaign }}
                                        @if ($baris->campaign->tanggalSelesai->lt(today()))
                                            <div class="teks-redup">Berakhir {{ $baris->campaign->tanggalSelesai->format('d/m/Y') }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $baris->campaign->periodeUnggah() ?? '-' }}</td>
                                    <td>
                                        {{-- Satu blok per platform: nama akun, status draft, dan status insight --}}
                                        @foreach ($baris->daftarPlatform as $item)
                                            <div @class(['mt-2' => ! $loop->first])>
                                                <strong>{{ $item->nama }}</strong>
                                                <span class="teks-redup">{{ $item->akun ? '@' . $item->akun->username : 'Belum ada akun' }}</span>
                                                <div class="status-platform">
                                                    <span class="teks-redup">Draft:</span>
                                                    @if ($item->hasil)
                                                        <x-status jenis="statusDraft" :nilai="$item->hasil->statusDraft" />
                                                    @else
                                                        <span class="teks-redup">Belum dikirim</span>
                                                    @endif

                                                    <span class="teks-redup">Insight:</span>
                                                    @if ($item->hasil)
                                                        <x-status jenis="statusInsight" :nilai="$item->hasil->statusInsight" />
                                                    @else
                                                        <span class="teks-redup">-</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </td>
                                    <td class="kolom-aksi">
                                        <x-aksi ikon="eye" label="Buka Pengerjaan" :href="route('influencer.hasil', ['pilih' => $baris->pendaftaran->idPendaftaran])" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endunless

    {{-- ===== DETAIL CAMPAIGN YANG DIPILIH ===== --}}
    @if ($terpilih)
        @php
            $campaign = $terpilih->campaign;
            $sudahBerakhir = $campaign->tanggalSelesai->lt(today());
            $pesanDitutup = 'Pengiriman sudah ditutup karena campaign berakhir pada ' . $campaign->tanggalSelesai->format('d/m/Y') . '.';
        @endphp

        <section>
            <a href="{{ request()->fullUrlWithoutQuery(['pilih', 'platform']) }}" class="tautan-kembali mb-3">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Kembali ke Campaign yang Diikuti
            </a>

            <h2 class="judul-bagian">{{ $campaign->namaCampaign }}</h2>

            {{-- ===== JADWAL (berlaku untuk semua platform di campaign ini) ===== --}}
            @if ($sudahBerakhir)
                <div class="catatan mb-3">
                    <strong><i class="bi bi-exclamation-circle" aria-hidden="true"></i> Campaign sudah berakhir</strong>
                    <p class="mb-0">
                        Campaign ini berakhir pada {{ $campaign->tanggalSelesai->format('d/m/Y') }}, sehingga draft, link, dan insight tidak dapat dikirim lagi.
                        @if ($campaign->periodeUnggah())
                            Pengiriman yang belum selesai hingga tanggal tersebut membuat akun diberi status blacklist secara otomatis.
                        @endif
                    </p>
                </div>
            @elseif ($campaign->periodeUnggah())
                <div class="catatan mb-3">
                    <strong><i class="bi bi-calendar-event" aria-hidden="true"></i> Jadwal campaign</strong>
                    <p class="mb-0">
                        Unggah konten pada <strong>{{ $campaign->periodeUnggah() }}</strong> lalu kirim link-nya.
                        Insight dikirim {{ config('aura.hariTungguInsight') }} hari setelah link dikirim,
                        paling lambat <strong>{{ $campaign->tanggalSelesai->format('d/m/Y') }}</strong>.
                        Jika pengiriman belum selesai hingga campaign berakhir, akun akan diberi status blacklist secara otomatis.
                    </p>
                </div>
            @endif

            {{-- ===== TAB PLATFORM (hanya tampil kalau campaign punya lebih dari satu platform) ===== --}}
            @if ($terpilih->daftarPlatform->count() > 1)
                <ul class="nav tab-platform" role="tablist">
                    @foreach ($terpilih->daftarPlatform as $item)
                        <li class="nav-item" role="presentation">
                            <button type="button" id="tab-{{ $item->kunci }}"
                                    @class(['nav-link', 'active' => $item->kunci === $platformAktif])
                                    data-bs-toggle="tab" data-bs-target="#panel-{{ $item->kunci }}"
                                    data-tab-platform="{{ $item->kunci }}"
                                    role="tab" aria-controls="panel-{{ $item->kunci }}"
                                    aria-selected="{{ $item->kunci === $platformAktif ? 'true' : 'false' }}">
                                {{ $item->nama }}
                                @if ($item->hasil?->statusInsight === 'disetujui')
                                    <i class="bi bi-check-circle-fill" aria-label="Selesai"></i>
                                @endif
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif

            <div class="tab-content">
                @foreach ($terpilih->daftarPlatform as $item)
                    @php
                        $hasil = $item->hasil;
                        $sudahSelesai = $hasil && $hasil->statusInsight === 'disetujui';
                    @endphp

                    <div id="panel-{{ $item->kunci }}" role="tabpanel" aria-labelledby="tab-{{ $item->kunci }}" tabindex="0"
                         @class(['tab-pane', 'fade', 'show active' => $item->kunci === $platformAktif])>

                        @if ($sudahSelesai)
                            <div class="catatan mb-3">
                                <strong><i class="bi bi-check-circle" aria-hidden="true"></i> Konten {{ $item->nama }} sudah selesai</strong>
                                <p class="mb-0">Insight sudah disetujui Admin. Tidak ada yang perlu dikirim lagi untuk platform ini.</p>
                            </div>
                        @endif

                        <div class="panel-detail">
                            {{-- ===== 1. DRAFT KONTEN (Tabel 18) ===== --}}
                            <div class="panel-detail-utama">
                                <div class="kepala-langkah">
                                    <p class="label-grup">1. Draft Konten {{ $item->nama }}</p>
                                    @if ($hasil)
                                        <x-status jenis="statusDraft" :nilai="$hasil->statusDraft" />
                                    @endif
                                </div>

                                @if ($hasil)
                                    <div class="field">
                                        <span class="field-label">Berkas Draft</span>
                                        <div class="kotak-berkas">
                                            @if ($hasil->draftKonten)
                                                <span>Draft konten ({{ strtoupper(pathinfo($hasil->draftKonten, PATHINFO_EXTENSION)) }})</span>
                                                <a class="tautan" href="{{ route('berkas.hasil', [$hasil, 'draft']) }}" target="_blank" rel="noopener">Lihat</a>
                                            @else
                                                <span class="teks-redup">Berkas draft sudah dihapus otomatis setelah link konten dikirim.</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="field">
                                        <span class="field-label">Tanggal Kirim Draft</span>
                                        <div class="kotak-berkas">{{ $hasil->tanggalPengirimanDraft->format('d/m/Y H:i') }} WIB</div>
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

                                @if (! $item->akun)
                                    <div class="catatan">Anda belum memiliki akun {{ $item->nama }}. Tambahkan akun di menu Profil untuk mengirim draft platform ini.</div>
                                @elseif (! $hasil || $hasil->statusDraft === 'revisi')
                                    @if ($sudahBerakhir)
                                        {{-- Campaign berakhir: form draft ditutup --}}
                                        <div class="catatan">{{ $pesanDitutup }}</div>
                                    @else
                                        {{-- Form kirim draft: pertama kali, atau kirim ulang setelah revisi.
                                             enctype multipart/form-data wajib supaya berkas ikut terkirim. --}}
                                        <form method="POST" action="{{ route('influencer.hasil.draft', $terpilih->pendaftaran) }}"
                                              enctype="multipart/form-data" class="tumpukan-kecil"
                                              data-konfirmasi="Kirim draft ini ke Admin?">
                                            @csrf
                                            <input type="hidden" name="platform" value="{{ $item->nama }}">

                                            <div class="field">
                                                <label for="draftKonten-{{ $item->kunci }}" class="field-label">{{ $hasil ? 'Unggah Draft Revisi' : 'Unggah Berkas Draft' }}</label>
                                                <input type="file" id="draftKonten-{{ $item->kunci }}" name="draftKonten" accept=".mp4,.mov,.webm,.jpg,.jpeg,.png"
                                                       data-maks-video="{{ config('aura.berkas.draft.maksimal') / 1024 }}"
                                                       data-maks-foto="{{ config('aura.berkas.draft.maksimalFoto') / 1024 }}"
                                                       @class(['input-aura', 'input-berkas', 'is-invalid' => $errors->has('draftKonten')])>
                                                @error('draftKonten')
                                                    <p class="pesan-error">{{ $message }}</p>
                                                @enderror
                                                <p class="teks-bantuan">Video (mp4, mov, webm) maksimal 100 MB, atau foto (jpg, png) maksimal 10 MB.</p>
                                                @if ($campaign->kontenSama)
                                                    <div class="catatan">
                                                        Campaign ini memakai konten yang sama untuk semua platform. Draft yang Anda kirim berlaku untuk
                                                        {{ $terpilih->daftarPlatform->pluck('nama')->join(', ', ' dan ') }}.
                                                    </div>
                                                @endif
                                            </div>

                                            <button type="submit" class="btn-aura btn-aura-primary">{{ $hasil ? 'Kirim Ulang Draft' : 'Kirim Draft' }}</button>
                                        </form>
                                    @endif
                                @else
                                    {{-- Draft sedang diperiksa atau sudah disetujui: cukup keterangan status --}}
                                    <div class="catatan">
                                        @if ($hasil->statusDraft === 'menunggu')
                                            Draft sedang diperiksa Admin. Anda akan bisa mengirim ulang kalau Admin meminta revisi.
                                        @elseif ($sudahSelesai || $sudahBerakhir || $hasil->linkKonten)
                                            Draft sudah disetujui.
                                        @else
                                            Draft sudah disetujui. Unggah konten ke akun {{ $item->nama }} Anda
                                            @if ($campaign->periodeUnggah())
                                                pada <strong>{{ $campaign->periodeUnggah() }}</strong>,
                                            @endif
                                            lalu kirim link-nya di bagian 2.
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="garis-tegak" aria-hidden="true"></div>

                            {{-- ===== 2. LINK DAN INSIGHT (Tabel 22)
                                 Tahap A: kirim link (draft lalu dihapus). Tahap B: kirim insight setelah masa tunggu. ===== --}}
                            <div class="panel-detail-utama">
                                <div class="kepala-langkah">
                                    <p class="label-grup">2. Link dan Insight {{ $item->nama }}</p>
                                    @if ($hasil && $hasil->linkKonten)
                                        <x-status jenis="statusInsight" :nilai="$hasil->statusInsight" />
                                    @endif
                                </div>

                                @if (! $hasil || $hasil->statusDraft !== 'disetujui')
                                    <div class="catatan">
                                        {{ $sudahBerakhir ? $pesanDitutup : 'Form link konten terbuka setelah draft disetujui Admin.' }}
                                    </div>

                                @elseif (! $hasil->linkKonten)
                                    {{-- Tahap A: kirim link konten --}}
                                    @if ($sudahBerakhir)
                                        <div class="catatan">{{ $pesanDitutup }}</div>
                                    @else
                                        <form method="POST" action="{{ route('influencer.hasil.link', $hasil) }}" class="tumpukan-kecil"
                                              data-konfirmasi="Kirim link konten {{ $item->nama }}? Berkas draft akan dihapus dari sistem setelah link dikirim." novalidate>
                                            @csrf

                                            <x-input name="linkKonten" label="Link Konten yang Sudah Tayang" type="url" placeholder="Tempel tautan konten" />

                                            <p class="teks-bantuan">
                                                Setelah link dikirim, berkas draft dihapus otomatis untuk menghemat penyimpanan.
                                                Insight dapat dikirim {{ config('aura.hariTungguInsight') }} hari setelah link dikirim,
                                                karena data insight baru lengkap setelah konten tayang selama {{ config('aura.hariTungguInsight') }} hari.
                                            </p>

                                            <button type="submit" class="btn-aura btn-aura-primary">Kirim Link</button>
                                        </form>
                                    @endif

                                @else
                                    {{-- Link sudah dikirim --}}
                                    <div class="field">
                                        <span class="field-label">Link Konten</span>
                                        <div class="kotak-berkas">
                                            @if (\Illuminate\Support\Str::startsWith($hasil->linkKonten, ['https://', 'http://']))
                                                <a class="tautan" href="{{ $hasil->linkKonten }}" target="_blank" rel="noopener noreferrer">Buka konten yang tayang</a>
                                            @endif
                                            @if ($hasil->tanggalPengirimanLink)
                                                <span class="teks-redup">Dikirim {{ $hasil->tanggalPengirimanLink->format('d/m/Y H:i') }} WIB</span>
                                            @endif
                                        </div>
                                    </div>

                                    @if (in_array($hasil->statusInsight, ['belum_dikirim', 'ditolak'], true))
                                        @if (! $hasil->bolehKirimInsight())
                                            {{-- Tahap B dikunci: masa tunggu insight belum lewat --}}
                                            <div class="catatan">
                                                <strong><i class="bi bi-hourglass-split" aria-hidden="true"></i> Menunggu masa insight</strong>
                                                <p class="mb-0">
                                                    Insight dapat dikirim mulai <strong>{{ $hasil->tanggalBolehKirimInsight()->format('d/m/Y') }}</strong>,
                                                    yaitu {{ config('aura.hariTungguInsight') }} hari setelah link dikirim.
                                                    Batas pengiriman insight: <strong>{{ $campaign->tanggalSelesai->format('d/m/Y') }}</strong>.
                                                </p>
                                            </div>
                                        @elseif ($sudahBerakhir)
                                            <div class="catatan">{{ $pesanDitutup }}</div>
                                        @else
                                            {{-- Tahap B: kirim insight --}}
                                            <form method="POST" action="{{ route('influencer.hasil.insight', $hasil) }}"
                                                  enctype="multipart/form-data" class="tumpukan-kecil"
                                                  data-konfirmasi="Kirim insight {{ $item->nama }} ini ke Admin?" novalidate>
                                                @csrf

                                                <div class="field">
                                                    <label for="fileInsight-{{ $item->kunci }}" class="field-label">Tangkapan Layar Insight</label>
                                                    <input type="file" id="fileInsight-{{ $item->kunci }}" name="fileInsight" accept=".jpg,.jpeg,.png,.webp"
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
                                                    <div class="col-4"><x-input name="jumlahShare" label="Jumlah Share" type="number" min="0" :value="$hasil->jumlahShare" placeholder="0" /></div>
                                                    <div class="col-4"><x-input name="jumlahSave" label="Jumlah Save" type="number" min="0" :value="$hasil->jumlahSave" placeholder="0" /></div>
                                                    <div class="col-4"><x-input name="jumlahPengikut" label="Jumlah Pengikut Saat Ini" type="number" min="1" :value="$hasil->jumlahPengikut ?? $item->akun?->jumlahPengikut" placeholder="0" /></div>
                                                </div>

                                                <p class="teks-bantuan">
                                                    Isi jumlah pengikut akun {{ $item->nama }} sesuai kondisi saat insight dikirim.
                                                    Batas pengiriman insight: <strong>{{ $campaign->tanggalSelesai->format('d/m/Y') }}</strong>.
                                                </p>

                                                <button type="submit" class="btn-aura btn-aura-primary">{{ $hasil->statusInsight === 'ditolak' ? 'Kirim Ulang Insight' : 'Kirim Insight' }}</button>
                                            </form>
                                        @endif
                                    @else
                                        {{-- Insight sudah dikirim: tampilkan yang tersimpan --}}
                                        <div class="field">
                                            <span class="field-label">Tangkapan Layar Insight</span>
                                            <div class="kotak-berkas">
                                                <a class="tautan" href="{{ route('berkas.hasil', [$hasil, 'insight']) }}" target="_blank" rel="noopener">Lihat tangkapan layar</a>
                                                @if ($hasil->tanggalPengirimanInsight)
                                                    <span class="teks-redup">Dikirim {{ $hasil->tanggalPengirimanInsight->format('d/m/Y H:i') }} WIB</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="metrik">
                                            <div class="kotak-angka"><span>Views</span><strong>{{ number_format($hasil->jumlahViews, 0, ',', '.') }}</strong></div>
                                            <div class="kotak-angka"><span>Likes</span><strong>{{ number_format($hasil->jumlahLikes, 0, ',', '.') }}</strong></div>
                                            <div class="kotak-angka"><span>Komentar</span><strong>{{ number_format($hasil->jumlahKomentar, 0, ',', '.') }}</strong></div>
                                            <div class="kotak-angka"><span>Share</span><strong>{{ $hasil->jumlahShare !== null ? number_format($hasil->jumlahShare, 0, ',', '.') : '-' }}</strong></div>
                                            <div class="kotak-angka"><span>Save</span><strong>{{ $hasil->jumlahSave !== null ? number_format($hasil->jumlahSave, 0, ',', '.') : '-' }}</strong></div>
                                            <div class="kotak-angka"><span>Pengikut</span><strong>{{ $hasil->jumlahPengikut !== null ? number_format($hasil->jumlahPengikut, 0, ',', '.') : '-' }}</strong></div>
                                        </div>
                                        <p class="teks-bantuan">
                                            {{ $hasil->statusInsight === 'menunggu' ? 'Insight sedang diperiksa Admin.' : 'Insight disetujui. Pembayaran fee akan diproses Admin.' }}
                                        </p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection