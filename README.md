# Aura Community

Aplikasi manajemen influencer berbasis web untuk mendukung pengelolaan campaign pada Aura Community (CV Aura Digital Kreatif). Dibangun sebagai bagian dari skripsi Program Studi Sistem Informasi, Fakultas Teknologi Informasi, Universitas Tarumanagara.

## Fitur

| Peran | Fitur |
|---|---|
| Guest | Landing page (layanan, portofolio, testimoni, galeri, kontak WhatsApp) |
| Influencer | Registrasi dengan demografi audiens, status verifikasi, daftar campaign, kirim draft dan insight, riwayat, profil dan rekening |
| Owner Brand | Campaign yang diberi akses, perkembangan campaign, laporan hasil, riwayat, profil |
| Admin | Verifikasi akun, data influencer dan blacklist, akun owner brand, campaign dan akses, seleksi pendaftar (kecocokan, ER, view rate, estimasi jangkauan, indikasi keaslian pengikut), pemeriksaan draft dan insight, pembayaran via Xendit, laporan, riwayat, profil |

## Teknologi

- PHP 8.3, Laravel 13, MySQL (Laragon)
- Bootstrap 5.3 dan Bootstrap Icons (CDN), font Inter dan Source Serif 4
- Xendit Payouts API v2 (mode Test) untuk pembayaran fee influencer

## Instalasi

1. Letakkan folder di `C:\laragon\www\aura-community-1`, lalu pasang dependensi:
   ```
   composer install
   copy .env.example .env
   php artisan key:generate
   ```

2. Atur `.env` (setiap nama variabel hanya satu kali):
   ```
   APP_NAME="Aura Community"
   APP_URL=http://127.0.0.1:8001
   APP_LOCALE=id
   APP_TIMEZONE=Asia/Jakarta

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=aura_community_1
   DB_USERNAME=root
   DB_PASSWORD=

   SESSION_DRIVER=file
   SESSION_COOKIE=aura1_session
   CACHE_STORE=file
   QUEUE_CONNECTION=sync

   AUTH_MODEL=App\Models\Pengguna

   AURA_WHATSAPP=
   AURA_WHATSAPP_TAMPIL="+62 8xx-xxxx-xxxx"

   XENDIT_SECRET_KEY=
   XENDIT_URL=https://api.xendit.co
   ```

3. Buat database: buka HeidiSQL (Laragon, tombol Database), lalu jalankan `database/sql/aura_community_1.sql` dari atas sampai bawah. Urutan isi file tidak boleh diubah, karena bagian bawah memperbarui data dari bagian atas.

4. Siapkan folder berkas privat dan gambar contoh untuk data demo:
   ```
   storage/app/private/draft/
   storage/app/private/insight/
   storage/app/private/demografi/
   ```
   Data contoh merujuk ke `demografi/contoh-demografi.png`. Taruh satu gambar dengan nama itu di folder `demografi` supaya tautan screenshot insight audiens tidak menampilkan 404.

5. Atur batas upload di `php.ini` (menu Laragon, PHP, php.ini):
   ```
   upload_max_filesize = 100M
   post_max_size = 110M
   ```

6. Jalankan:
   ```
   php artisan config:clear
   php artisan serve --port=8001
   ```

Buka `http://127.0.0.1:8001`.

## Akun uji

| Peran | Email | Password |
|---|---|---|
| Admin | admin@aura.co | admin123 |
| Owner Brand | rina@contoh.com, bagas@contoh.com | password123 |
| Influencer | salsa@contoh.com, dimas@contoh.com, putri@contoh.com | password123 |
| Influencer (menunggu verifikasi) | kevin@contoh.com, nadia@contoh.com | password123 |
| Influencer (blacklist) | fajar@contoh.com | password123 |

Campaign **TikTok Lip Tint GlowUp** berisi tujuh pendaftar contoh untuk mendemokan halaman seleksi pendaftar (kecocokan, label perlu diperiksa, dan estimasi jangkauan).

## Seleksi pendaftar

Halaman detail campaign (Admin) menampilkan data pendukung seleksi untuk setiap pendaftar:

- **Kecocokan**: jumlah kriteria yang terpenuhi dari kategori, lokasi, target usia, dan minimal pengikut. Lokasi "Nasional" atau "Indonesia" dianggap cocok dengan semua domisili.
- **ER dan view rate**: dihitung dari insight campaign sebelumnya yang sudah disetujui.
- **Estimasi jangkauan**: pengikut x view rate historis. Total untuk campaign dihitung dari influencer yang diterima.
- **Label perlu diperiksa**: muncul jika view rate kurang dari 50% rata-rata akun lain pada platform dan rentang pengikut yang sama.

Ambang label dan rentang pengikut dapat diubah di `config/aura.php` bagian `seleksi`.

## Blacklist otomatis

Influencer yang diterima di campaign tetapi tidak menyelesaikan pengiriman konten hingga campaign berakhir akan otomatis diberi status blacklist. Aturan ini hanya berlaku untuk campaign yang memiliki periode unggah.

Pengecekan berjalan dengan tiga cara:

1. Otomatis setiap kali Admin membuka menu **Campaign** atau **Data Influencer**.
2. Manual lewat terminal:
   ```
   php artisan aura:blacklist-otomatis
   ```
3. Terjadwal setiap hari pukul 00.05 (didaftarkan di `routes/console.php`). Di server lokal, jadwal ini hanya berjalan kalau scheduler dinyalakan:
   ```
   php artisan schedule:work
   ```

Influencer yang draft atau insight-nya masih menunggu pemeriksaan Admin tidak diberi blacklist. Admin dapat mencabut status blacklist melalui halaman detail Data Influencer.

## Xendit (mode Test)

1. Dashboard Xendit mode **Test**, lalu Settings, Developers, API Keys, Generate Secret Key dengan izin **Write** untuk Money-out.
2. Isi `XENDIT_SECRET_KEY` di `.env` (diawali `xnd_development_`), lalu `php artisan config:clear`.
3. Nomor rekening simulasi: nomor biasa berhasil, `121212` gagal (INVALID_DESTINATION), `999999` gagal (REJECTED_BY_CHANNEL).

Status akhir pembayaran diperbarui lewat tombol **Cek Status** di halaman Pembayaran, karena webhook tidak bisa menjangkau server lokal. Pembayaran hanya dapat diproses untuk influencer yang seluruh hasil pengerjaan dan insight-nya telah disetujui.

## Struktur singkat

```
app/Http/Controllers/{Admin,Influencer,OwnerBrand}   controller per peran
app/Http/Requests                                    validasi form
app/Models                                           14 model sesuai Class Diagram
app/Services                                         RekapCampaign, SeleksiInfluencer, BlacklistOtomatis, XenditPayout
app/Console/Commands                                 perintah aura:blacklist-otomatis
config/aura.php, landing.php, menu.php               pilihan tetap, isi landing page, menu sidebar
resources/views                                      layout, komponen, halaman per peran
resources/views/partials/input-demografi.blade.php   input demografi audiens (registrasi, profil, form Admin)
public/css, public/js                                gaya dan perilaku halaman
database/sql/aura_community_1.sql                    struktur database dan data contoh
```

## Keamanan

- Jangan pernah meng-commit `.env`. File ini berisi secret key Xendit.
- Berkas draft, insight, dan screenshot demografi disimpan di `storage/app/private` dan hanya bisa dibuka Admin atau influencer pemiliknya.

## Lisensi

Dibuat untuk keperluan skripsi dan operasional internal Aura Community.