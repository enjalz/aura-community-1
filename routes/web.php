<?php

use App\Http\Controllers\Admin\AksesCampaignController;
use App\Http\Controllers\Admin\CampaignController as AdminCampaignController;
use App\Http\Controllers\Admin\DataInfluencerController;
use App\Http\Controllers\Admin\DataOwnerBrandController;
use App\Http\Controllers\Admin\HasilCampaignController;
use App\Http\Controllers\Admin\LaporanController as AdminLaporanController;
use App\Http\Controllers\Admin\PembayaranController;
use App\Http\Controllers\Admin\PendaftarCampaignController;
use App\Http\Controllers\Admin\ProfilController as AdminProfilController;
use App\Http\Controllers\Admin\RiwayatController as AdminRiwayatController;
use App\Http\Controllers\Admin\VerifikasiAkunController;
use App\Http\Controllers\Admin\VerifikasiInsightController;
use App\Http\Controllers\BerkasController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Influencer\CampaignController as InfluencerCampaignController;
use App\Http\Controllers\Influencer\HasilPengerjaanController;
use App\Http\Controllers\Influencer\ProfilController as InfluencerProfilController;
use App\Http\Controllers\Influencer\RiwayatController as InfluencerRiwayatController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OwnerBrand\CampaignController as OwnerCampaignController;
use App\Http\Controllers\OwnerBrand\LaporanController as OwnerLaporanController;
use App\Http\Controllers\OwnerBrand\ProfilController as OwnerProfilController;
use App\Http\Controllers\OwnerBrand\RiwayatController as OwnerRiwayatController;
use App\Http\Controllers\RegistrasiController;
use Illuminate\Support\Facades\Route;

// Landing page untuk Guest (Tabel 4)
Route::view('/', 'landing')->name('beranda');

// Hanya untuk pengunjung yang belum login
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'tampilkan'])->name('login');
    Route::post('/login', [LoginController::class, 'masuk'])->name('login.masuk');

    // Registrasi Akun Influencer (Tabel 5)
    Route::get('/register', [RegistrasiController::class, 'tampilkan'])->name('register');
    Route::post('/register', [RegistrasiController::class, 'simpan'])->name('register.simpan');
}); // tutup grup guest

// Untuk pengguna yang sudah login
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'keluar'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'arahkan'])->name('dashboard');

    // Membuka berkas draft/insight yang disimpan privat
    Route::get('/berkas/hasil/{hasil}/{jenis}', [BerkasController::class, 'tampil'])
        ->whereIn('jenis', ['draft', 'insight'])
        ->name('berkas.hasil');

    // ===== ADMIN =====
    Route::middleware('peran:admin')->prefix('admin')->name('admin.')->group(function () {
        // Melihat Dashboard (Tabel 6)
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

        // Verifikasi Akun (Tabel 8)
        Route::get('/verifikasi-akun', [VerifikasiAkunController::class, 'index'])->name('verifikasi');
        Route::post('/verifikasi-akun/{influencer}/setujui', [VerifikasiAkunController::class, 'setujui'])->name('verifikasi.setujui');
        Route::post('/verifikasi-akun/{influencer}/tolak', [VerifikasiAkunController::class, 'tolak'])->name('verifikasi.tolak');

        // Data Influencer (Tabel 10) dan Blacklist (Tabel 11)
        Route::controller(DataInfluencerController::class)->prefix('data-influencer')->name('influencer')->group(function () {
            Route::get('/', 'index');
            Route::get('/tambah', 'create')->name('.tambah');
            Route::post('/', 'store')->name('.simpan');
            Route::get('/{influencer}', 'show')->name('.detail');
            Route::get('/{influencer}/ubah', 'edit')->name('.ubah');
            Route::put('/{influencer}', 'update')->name('.perbarui');
            Route::delete('/{influencer}', 'destroy')->name('.hapus');
            Route::post('/{influencer}/blacklist', 'blacklist')->name('.blacklist');
            Route::post('/{influencer}/cabut-blacklist', 'cabutBlacklist')->name('.cabut-blacklist');
        }); // tutup grup data influencer

        // Membuat Akun Owner Brand (Tabel 12) dan Mengelola Data Owner Brand (Tabel 13)
        Route::controller(DataOwnerBrandController::class)->prefix('data-owner-brand')->name('owner-brand')->group(function () {
            Route::get('/', 'index');
            Route::get('/buat', 'create')->name('.buat');
            Route::post('/', 'store')->name('.simpan');
            Route::get('/{ownerBrand}', 'show')->name('.detail');
            Route::get('/{ownerBrand}/ubah', 'edit')->name('.ubah');
            Route::put('/{ownerBrand}', 'update')->name('.perbarui');
            Route::delete('/{ownerBrand}', 'destroy')->name('.hapus');
        }); // tutup grup data owner brand

        // Mengelola Campaign (Tabel 14)
        Route::controller(AdminCampaignController::class)->prefix('campaign')->name('campaign')->group(function () {
            Route::get('/', 'index');
            Route::get('/tambah', 'create')->name('.tambah');
            Route::post('/', 'store')->name('.simpan');
            Route::get('/{campaign}', 'show')->name('.detail');
            Route::get('/{campaign}/ubah', 'edit')->name('.ubah');
            Route::put('/{campaign}', 'update')->name('.perbarui');
            Route::delete('/{campaign}', 'destroy')->name('.hapus');
        }); // tutup grup campaign

        // Memberikan Akses Campaign (Tabel 15)
        Route::controller(AksesCampaignController::class)->prefix('campaign/{campaign}/akses')->name('campaign.akses')->group(function () {
            Route::get('/', 'edit');
            Route::put('/', 'update')->name('.simpan');
        }); // tutup grup akses campaign

        // Pemilihan influencer pendaftar campaign (Batasan 7)
        Route::controller(PendaftarCampaignController::class)->prefix('campaign/{campaign}/pendaftar/{pendaftaran}')->name('campaign.pendaftar')->group(function () {
            Route::post('/terima', 'terima')->name('.terima');
            Route::post('/tolak', 'tolak')->name('.tolak');
        }); // tutup grup pendaftar campaign

        // Memeriksa Hasil (Tabel 19), Memberikan Feedback (Tabel 20), Menyetujui Pengerjaan (Tabel 21)
        Route::controller(HasilCampaignController::class)->prefix('hasil-campaign')->name('hasil')->group(function () {
            Route::get('/', 'index');
            Route::post('/{hasil}/feedback', 'feedback')->name('.feedback');
            Route::post('/{hasil}/setujui', 'setujui')->name('.setujui');
        }); // tutup grup hasil campaign

        // Memeriksa Insight Campaign (Tabel 23)
        Route::controller(VerifikasiInsightController::class)->prefix('verifikasi-insight')->name('insight')->group(function () {
            Route::get('/', 'index');
            Route::post('/{hasil}/setujui', 'setujui')->name('.setujui');
            Route::post('/{hasil}/tolak', 'tolak')->name('.tolak');
        }); // tutup grup verifikasi insight

        // Memproses Pembayaran Campaign (Tabel 24)
        Route::controller(PembayaranController::class)->prefix('pembayaran')->name('pembayaran')->group(function () {
            Route::get('/', 'index');
            Route::post('/{pendaftaran}/proses', 'proses')->name('.proses');
            Route::post('/status/{pembayaran}', 'cekStatus')->name('.cek-status');
        }); // tutup grup pembayaran

        // Laporan Hasil Campaign (Tabel 25)
        Route::get('/laporan', [AdminLaporanController::class, 'index'])->name('laporan');
        Route::get('/laporan/{campaign}', [AdminLaporanController::class, 'detail'])->name('laporan.detail');
        Route::post('/laporan/{campaign}', [AdminLaporanController::class, 'simpan'])->name('laporan.simpan');

        // Riwayat Campaign (Tabel 26)
        Route::get('/riwayat', [AdminRiwayatController::class, 'index'])->name('riwayat');

        // Mengelola Profil (Tabel 7)
        Route::get('/profil', [AdminProfilController::class, 'edit'])->name('profil');
        Route::put('/profil', [AdminProfilController::class, 'update'])->name('profil.perbarui');
    }); // tutup grup admin

    // ===== OWNER BRAND =====
    Route::middleware('peran:owner_brand')->prefix('owner')->name('owner.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'ownerBrand'])->name('dashboard');   // Tabel 6
        Route::get('/campaign', [OwnerCampaignController::class, 'index'])->name('campaign');     // Tabel 16
        Route::get('/laporan', [OwnerLaporanController::class, 'index'])->name('laporan');        // Tabel 25
        Route::get('/riwayat', [OwnerRiwayatController::class, 'index'])->name('riwayat');        // Tabel 26
        Route::get('/profil', [OwnerProfilController::class, 'edit'])->name('profil');            // Tabel 7
        Route::put('/profil', [OwnerProfilController::class, 'update'])->name('profil.perbarui');
        Route::get('/laporan', [OwnerLaporanController::class, 'index'])->name('laporan');        // Tabel 25
        Route::get('/laporan/{campaign}', [OwnerLaporanController::class, 'detail'])->name('laporan.detail'); // Tabel 25 langkah 6-7
    }); // tutup grup owner brand

    // ===== INFLUENCER =====
    Route::middleware('peran:influencer')->prefix('influencer')->name('influencer.')->group(function () {
        // Melihat Status Verifikasi (Tabel 9), bisa dibuka sebelum akun diverifikasi
        Route::get('/status-verifikasi', [DashboardController::class, 'statusVerifikasi'])->name('status');

        Route::middleware('terverifikasi')->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'influencer'])->name('dashboard');   // Tabel 6

            // Melihat Detail Campaign (Tabel 16) dan Mendaftar Campaign (Tabel 17)
            Route::get('/campaign', [InfluencerCampaignController::class, 'index'])->name('campaign');
            Route::post('/campaign/{campaign}/daftar', [InfluencerCampaignController::class, 'daftar'])->name('campaign.daftar');

            // Mengirim Hasil Pengerjaan (Tabel 18) dan Mengirim Insight (Tabel 22)
            Route::controller(HasilPengerjaanController::class)->prefix('hasil-pengerjaan')->name('hasil')->group(function () {
                Route::get('/', 'index');
                Route::post('/{pendaftaran}/draft', 'kirimDraft')->name('.draft');
                Route::post('/insight/{hasil}', 'kirimInsight')->name('.insight');
            }); // tutup grup hasil pengerjaan

            // Riwayat Campaign (Tabel 26)
            Route::get('/riwayat', [InfluencerRiwayatController::class, 'index'])->name('riwayat');

            // Mengelola Profil (Tabel 7)
            Route::get('/profil', [InfluencerProfilController::class, 'edit'])->name('profil');
            Route::put('/profil', [InfluencerProfilController::class, 'update'])->name('profil.perbarui');
        }); // tutup grup terverifikasi
    }); // tutup grup influencer
}); // tutup grup auth