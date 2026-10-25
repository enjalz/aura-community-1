{{-- Form Tambah dan Ubah Campaign (Tabel 14).
     Kiri: Informasi Campaign (Tabel 33). Kanan: Ketentuan Platform (Tabel 34), minimal satu baris.
     $campaign = null saat Tambah, berisi data saat Ubah. --}}
@extends('layouts.dashboard')

@php
    $ubah = $campaign !== null;
    $kembali = $ubah ? route('admin.campaign.detail', $campaign) : route('admin.campaign');

    // Isi baris ketentuan: dari input terakhir (kalau validasi gagal), dari database (saat Ubah),
    // atau satu baris kosong (saat Tambah)
    $daftarKetentuan = old('ketentuan', $ubah
        ? $campaign->ketentuanPlatform->map->only(['platform', 'jenisKonten', 'jumlahPostingan'])->all()
        : [['platform' => 'Instagram', 'jenisKonten' => 'video', 'jumlahPostingan' => 1]]);
@endphp

@section('title', ($ubah ? 'Ubah Campaign' : 'Tambah Campaign') . ' - Aura Community')
@section('judul', $ubah ? 'Ubah Campaign' : 'Tambah Campaign')

@push('scripts')
    <script src="{{ asset('js/ketentuan.js') }}"></script>
@endpush

@section('isi')
    <a href="{{ $kembali }}" class="tautan-kembali">
        <i class="bi bi-arrow-left" aria-hidden="true"></i>
        Kembali
    </a>

    <form method="POST" action="{{ $ubah ? route('admin.campaign.perbarui', $campaign) : route('admin.campaign.simpan') }}" novalidate>
        @csrf
        @if ($ubah)
            @method('PUT')
        @endif

        <div class="row g-4">
            {{-- ===== INFORMASI CAMPAIGN ===== --}}
            <div class="col-lg-7">
                <section class="form-kartu">
                    <h2 class="form-judul">Informasi Campaign</h2>

                    <div class="row g-3">
                        <div class="col-12">
                            <x-input name="namaCampaign" label="Nama Campaign" :value="$campaign?->namaCampaign" placeholder="Contoh: Campaign Serum Wajah" />
                        </div>
                        <div class="col-12">
                            <div class="field">
                                <label for="deskripsi" class="field-label">Deskripsi (brief dan instruksi pengerjaan)</label>
                                <textarea id="deskripsi" name="deskripsi" rows="5" placeholder="Tuliskan brief dan instruksi pembuatan konten"
                                          @class(['input-aura', 'is-invalid' => $errors->has('deskripsi')])>{{ old('deskripsi', $campaign?->deskripsi) }}</textarea>
                                @error('deskripsi')
                                    <p class="pesan-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <x-input name="targetAudiens" label="Target Audiens" :value="$campaign?->targetAudiens" placeholder="Contoh: Perempuan usia 18-30 tahun" />
                        </div>
                        <div class="col-md-6">
                            <div class="field">
                                <label for="kategoriInfluencer" class="field-label">Kategori Influencer</label>
                                <select id="kategoriInfluencer" name="kategoriInfluencer" @class(['input-aura', 'is-invalid' => $errors->has('kategoriInfluencer')])>
                                    <option value="" disabled @selected(! old('kategoriInfluencer', $campaign?->kategoriInfluencer))>Pilih kategori</option>
                                    @foreach (config('aura.kategori') as $item)
                                        <option value="{{ $item }}" @selected(old('kategoriInfluencer', $campaign?->kategoriInfluencer) === $item)>{{ $item }}</option>
                                    @endforeach
                                </select>
                                @error('kategoriInfluencer')
                                    <p class="pesan-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <x-input name="lokasiCampaign" label="Lokasi Campaign" :value="$campaign?->lokasiCampaign" placeholder="Contoh: Jakarta atau Nasional" />
                        </div>
                        <div class="col-md-6">
                            <x-input name="feePerInfluencer" label="Fee per Influencer (Rp)" type="number" min="0" step="1000"
                                     :value="$ubah ? (int) $campaign->feePerInfluencer : null" placeholder="Contoh: 150000" />
                        </div>
                        <div class="col-md-6">
                            <x-input name="tanggalMulai" label="Tanggal Mulai" type="date" :value="$campaign?->tanggalMulai?->format('Y-m-d')" />
                        </div>
                        <div class="col-md-6">
                            <x-input name="tanggalSelesai" label="Tanggal Selesai" type="date" :value="$campaign?->tanggalSelesai?->format('Y-m-d')" />
                        </div>
                        <div class="col-md-6">
                            <x-input name="kuotaInfluencer" label="Kuota Influencer" type="number" min="1" :value="$campaign?->kuotaInfluencer" placeholder="Contoh: 50" />
                        </div>
                        <div class="col-md-6">
                            <div class="field">
                                <label for="statusCampaign" class="field-label">Status</label>
                                <select id="statusCampaign" name="statusCampaign" @class(['input-aura', 'is-invalid' => $errors->has('statusCampaign')])>
                                    @foreach (config('aura.statusCampaign') as $nilai => $label)
                                        <option value="{{ $nilai }}" @selected(old('statusCampaign', $campaign?->statusCampaign ?? 'draft') === $nilai)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <p class="teks-bantuan">Influencer hanya dapat mendaftar saat status Berjalan.</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            {{-- ===== KETENTUAN PLATFORM ===== --}}
            <div class="col-lg-5">
                {{-- data-indeks-berikut: nomor untuk baris baru, supaya tidak bentrok dengan baris yang sudah ada --}}
                <section class="form-kartu" data-ketentuan data-indeks-berikut="{{ count($daftarKetentuan) }}">
                    <h2 class="form-judul">Ketentuan Platform</h2>
                    <p class="teks-bantuan">Minimal satu ketentuan. Contoh: Instagram, Video, 1 postingan.</p>

                    @error('ketentuan')
                        <div class="alert-aura alert-gagal" role="alert">{{ $message }}</div>
                    @enderror

                    <div class="daftar-ketentuan" data-ketentuan-daftar>
                        @foreach (array_values($daftarKetentuan) as $indeks => $baris)
                            @include('admin.campaign.baris-ketentuan', ['indeks' => $indeks, 'baris' => $baris])
                        @endforeach
                    </div>

                    <button type="button" class="btn-aura btn-aura-outline" data-tambah-ketentuan>
                        <i class="bi bi-plus-lg" aria-hidden="true"></i>
                        Tambah Ketentuan
                    </button>

                    {{-- Cetakan baris baru. <template> tidak tampil di halaman; isinya disalin oleh ketentuan.js. --}}
                    <template id="template-ketentuan">
                        @include('admin.campaign.baris-ketentuan', ['indeks' => '{indeks}', 'baris' => []])
                    </template>
                </section>
            </div>
        </div>

        <div class="form-aksi">
            <a href="{{ $kembali }}" class="btn-aura btn-aura-outline">Batal</a>
            <button type="submit" class="btn-aura btn-aura-primary">{{ $ubah ? 'Simpan Perubahan' : 'Simpan Campaign' }}</button>
        </div>
    </form>
@endsection