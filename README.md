# Aura Community

Aplikasi manajemen influencer berbasis web untuk mendukung pengelolaan campaign pada Aura Community (CV Aura Digital Kreatif).
Dibangun sebagai bagian dari skripsi Program Studi Sistem Informasi, Fakultas Teknologi Informasi, Universitas Tarumanagara.

## Fitur

| Peran | Fitur |
| --- | --- |
| Guest | Landing page (layanan, portofolio, testimoni, galeri, kontak WhatsApp) |
| Influencer | Registrasi, status verifikasi, daftar campaign, kirim draft dan insight, riwayat, profil dan rekening |
| Owner Brand | Campaign yang diberi akses, perkembangan campaign, laporan hasil, riwayat, profil |
| Admin | Verifikasi akun, data influencer dan blacklist, akun owner brand, campaign dan akses, pemilihan pendaftar, pemeriksaan draft dan insight, pembayaran via Xendit, laporan, riwayat, profil |

## Teknologi

- PHP 8.3, Laravel 13, MySQL (Laragon)
- Bootstrap 5.3 dan Bootstrap Icons (CDN), font Inter dan Source Serif 4
- Xendit Payouts API v2 (mode Test) untuk pembayaran fee influencer

## Instalasi

1. Letakkan folder di `C:\laragon\www\aura-community-1`, lalu pasang dependensi:

```bat
   composer install
   copy .env.example .env
   php artisan key:generate
```

2. Atur `.env` (setiap nama variabel hanya satu kali):

```env
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

3. Buat database: buka HeidiSQL (Laragon, tombol Database), lalu jalankan `database/sql/aura_community_1.sql`.

4. Atur batas upload di `php.ini` (menu Laragon, PHP, php.ini):

```ini
   upload_max_filesize = 100M
   post_max_size = 110M
```

5. Jalankan:

```bat
   php artisan config:clear
   php artisan serve --port=8001
```

   Buka `http://127.0.0.1:8001`.

## Akun uji

| Peran | Email | Password |
| --- | --- | --- |
| Admin | admin@aura.co | admin123 |
| Owner Brand | rina@contoh.com, bagas@contoh.com | password123 |
| Influencer | salsa@contoh.com, dimas@contoh.com, putri@contoh.com | password123 |
| Influencer (menunggu verifikasi) | kevin@contoh.com, nadia@contoh.com | password123 |
| Influencer (blacklist) | fajar@contoh.com | password123 |

## Xendit (mode Test)

1. Dashboard Xendit mode **Test**, lalu Settings, Developers, API Keys, Generate Secret Key dengan izin **Write** untuk Money-out.
2. Isi `XENDIT_SECRET_KEY` di `.env` (diawali `xnd_development_`), lalu `php artisan config:clear`.
3. Nomor rekening simulasi: nomor biasa berhasil, `121212` gagal (INVALID_DESTINATION), `999999` gagal (REJECTED_BY_CHANNEL).

Status akhir pembayaran diperbarui lewat tombol **Cek Status** di halaman Pembayaran, karena webhook tidak bisa menjangkau server lokal.

## Struktur singkat

```
app/Http/Controllers/{Admin,Influencer,OwnerBrand}   controller per peran
app/Http/Requests                                    validasi form
app/Models                                           14 model sesuai Class Diagram
app/Services                                         RekapCampaign, XenditPayout
config/aura.php, landing.php, menu.php               pilihan tetap, isi landing page, menu sidebar
resources/views                                      layout, komponen, halaman per peran
public/css, public/js                                gaya dan perilaku halaman
database/sql/aura_community_1.sql                    struktur database dan data contoh
```

## Keamanan

- Jangan pernah meng-commit `.env`. File ini berisi secret key Xendit.
- Berkas draft dan insight disimpan di `storage/app/private` dan hanya bisa dibuka Admin atau influencer pemiliknya.

## Dokumentasi

Penjelasan lengkap (database, hak akses, peta route, aturan bisnis per use case, tampilan, integrasi Xendit, keamanan, pengujian black-box, dan pemecahan masalah) ada di dokumen **Dokumentasi Teknis Aura Community**.

## Lisensi

Dibuat untuk keperluan skripsi dan operasional internal Aura Community.