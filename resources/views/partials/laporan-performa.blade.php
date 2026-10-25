{{-- Isi laporan yang sama untuk Admin dan Owner Brand (Tabel 25, Bab 2.1.4):
     ringkasan statistik, ringkasan engagement, performa per influencer, dan rincian konten.
     Dipanggil dengan @include('partials.laporan-performa').
     Variabel dari controller: $ringkasan, $performa, $rincian --}}
@php
    // Format persen gaya Indonesia, contoh 7,55%. Tanda "-" kalau tidak bisa dihitung.
    $persen = fn (?float $nilai) => $nilai !== null ? number_format($nilai, 2, ',', '.') . '%' : '-';
    // Format angka ribuan, contoh 18.400. Tanda "-" kalau kosong (data lama tanpa pengikut).
    $angka = fn ($nilai) => $nilai !== null ? number_format((int) $nilai, 0, ',', '.') : '-';
@endphp

{{-- Tabel 25 langkah 3: statistik diakumulasi otomatis --}}
<p class="label-grup">Ringkasan Statistik</p>
<x-kartu-statistik :ringkasan="$ringkasan" />

<p class="label-grup">Ringkasan Engagement</p>
<div class="statistik-grid statistik-grid-3">
    <div class="kartu-angka">
        <span>Rata-rata Engagement Rate</span>
        <strong>{{ $persen($performa['rataRataEr']) }}</strong>
    </div>
    <div class="kartu-angka">
        <span>Engagement Rate Keseluruhan</span>
        <strong>{{ $persen($performa['erKeseluruhan']) }}</strong>
    </div>
    <div class="kartu-angka">
        <span>Rata-rata View Rate</span>
        <strong>{{ $persen($performa['rataRataViewRate']) }}</strong>
    </div>
</div>

{{-- Performa per influencer, ER tertinggi di atas --}}
<p class="label-grup">Performa per Influencer</p>
@if ($performa['perInfluencer']->isEmpty())
    <div class="kosong">Belum ada insight yang disetujui untuk campaign ini.</div>
@else
    <p class="teks-bantuan">
        {{ $performa['diAtas'] }} influencer di atas rata-rata dan
        {{ $performa['diBawah'] }} influencer di bawah rata-rata.
    </p>
    <div class="tabel-wrap">
        <table class="tabel-aura">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Influencer</th>
                    <th scope="col">Jumlah Konten</th>
                    <th scope="col">Views</th>
                    <th scope="col">Likes</th>
                    <th scope="col">Komentar</th>
                    <th scope="col">ER</th>
                    <th scope="col">Performa</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($performa['perInfluencer'] as $baris)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $baris->nama }}</td>
                        <td>{{ $baris->jumlahKonten }}</td>
                        <td>{{ $angka($baris->views) }}</td>
                        <td>{{ $angka($baris->likes) }}</td>
                        <td>{{ $angka($baris->komentar) }}</td>
                        <td>{{ $persen($baris->er) }}</td>
                        <td>
                            @if ($baris->label === 'di_atas')
                                <strong>Di Atas Rata-rata</strong>
                            @elseif ($baris->label === 'di_bawah')
                                <span class="teks-redup">Di Bawah Rata-rata</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

{{-- Rincian setiap konten beserta tautannya (Tabel 25 langkah 7) --}}
<p class="label-grup">Rincian Konten ({{ $ringkasan['konten'] }})</p>
@if ($rincian->isEmpty())
    <div class="kosong">Belum ada konten dengan insight yang disetujui.</div>
@else
    <div class="tabel-wrap">
        <table class="tabel-aura">
            <thead>
                <tr>
                    <th scope="col">Influencer</th>
                    <th scope="col">Akun Media Sosial</th>
                    <th scope="col">Link Konten</th>
                    <th scope="col">Pengikut</th>
                    <th scope="col">Views</th>
                    <th scope="col">Likes</th>
                    <th scope="col">Komentar</th>
                    <th scope="col">ER</th>
                    <th scope="col">View Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rincian as $hasil)
                    <tr>
                        <td>{{ $hasil->pendaftaran->influencer->pengguna->nama }}</td>
                        <td>{{ $hasil->akunSosmed->platform }} - &#64;{{ $hasil->akunSosmed->username }}</td>
                        <td>
                            {{-- Keamanan: tautan hanya aktif kalau diawali http:// atau https:// --}}
                            @if (\Illuminate\Support\Str::startsWith($hasil->linkKonten, ['https://', 'http://']))
                                <a class="tautan" href="{{ $hasil->linkKonten }}" target="_blank" rel="noopener noreferrer">Buka Konten</a>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $angka($hasil->jumlahPengikut) }}</td>
                        <td>{{ $angka($hasil->jumlahViews) }}</td>
                        <td>{{ $angka($hasil->jumlahLikes) }}</td>
                        <td>{{ $angka($hasil->jumlahKomentar) }}</td>
                        <td>{{ $persen($hasil->engagementRate()) }}</td>
                        <td>{{ $persen($hasil->viewRate()) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $rincian->links('partials.paginasi', ['label' => 'konten']) }}
@endif

<div class="catatan">
    Engagement Rate (ER) = (Likes + Komentar) / Views × 100%. ER per influencer dihitung dari total seluruh kontennya.<br>
    View Rate = Views / Pengikut saat insight dikirim × 100%.<br>
    Performa ditentukan dengan membandingkan ER influencer terhadap rata-rata ER campaign.
</div>