{{-- Memberikan Akses Campaign (Tabel 15).
     Admin mencentang Owner Brand yang berhak melihat campaign ini di dashboard mereka. --}}
@extends('layouts.dashboard')

@section('title', 'Akses ' . $campaign->namaCampaign . ' - Aura Community')
@section('judul', 'Akses Campaign')

@section('isi')
    <a href="{{ route('admin.campaign.detail', $campaign) }}" class="tautan-kembali">
        <i class="bi bi-arrow-left" aria-hidden="true"></i>
        Kembali ke Detail Campaign
    </a>

    <form method="POST" action="{{ route('admin.campaign.akses.simpan', $campaign) }}" class="form-sempit">
        @csrf
        @method('PUT')

        <section class="form-kartu">
            {{-- Tabel 15 langkah 2: informasi campaign --}}
            <div>
                <h2 class="form-judul">{{ $campaign->namaCampaign }}</h2>
                <p class="teks-bantuan mt-1">
                    {{ $campaign->tanggalMulai->format('d/m/Y') }} - {{ $campaign->tanggalSelesai->format('d/m/Y') }}
                    · {{ config('aura.statusCampaign.' . $campaign->statusCampaign) }}
                </p>
            </div>

            <hr class="garis">

            {{-- Tabel 15 langkah 3: memilih Owner Brand --}}
            <p class="form-sub">Pilih Owner Brand yang dapat melihat campaign ini</p>

            @if ($daftarOwnerBrand->isEmpty())
                <div class="kosong">
                    Belum ada akun Owner Brand.
                    <a class="tautan" href="{{ route('admin.owner-brand.buat') }}">Buat akun Owner Brand</a>
                </div>
            @else
                <div class="daftar-pilihan">
                    @foreach ($daftarOwnerBrand as $ownerBrand)
                        {{-- name="ownerBrand[]": semua kotak yang dicentang dikirim sebagai satu array --}}
                        <label class="pilihan">
                            <input type="checkbox" name="ownerBrand[]" value="{{ $ownerBrand->idPengguna }}"
                                   @checked(in_array($ownerBrand->idPengguna, old('ownerBrand', $terpilih)))>
                            <span class="pilihan-teks">
                                <strong>{{ $ownerBrand->namaBrand }}</strong>
                                <span>{{ $ownerBrand->namaPerusahaan }} · {{ $ownerBrand->pengguna->email }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                <p class="teks-bantuan">Owner Brand yang tidak dicentang tidak dapat melihat campaign ini.</p>
            @endif
        </section>

        <div class="form-aksi">
            <a href="{{ route('admin.campaign.detail', $campaign) }}" class="btn-aura btn-aura-outline">Batal</a>
            <button type="submit" class="btn-aura btn-aura-primary">Simpan Akses</button>
        </div>
    </form>
@endsection