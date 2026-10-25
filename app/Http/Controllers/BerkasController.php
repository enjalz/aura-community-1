<?php

namespace App\Http\Controllers;

use App\Models\HasilPengerjaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

// Membuka berkas draft konten dan tangkapan layar insight.
// Berkas disimpan di storage/app/private (tidak bisa dibuka langsung dari browser),
// jadi setiap permintaan lewat sini dan hak aksesnya dicek lebih dulu.
class BerkasController extends Controller
{
    public function tampil(Request $request, HasilPengerjaan $hasil, string $jenis): Response
    {
        $pengguna = $request->user();

        // Yang boleh membuka: Admin, atau influencer pemilik hasil pengerjaan ini
        $boleh = $pengguna->peran() === 'admin'
            || $hasil->pendaftaran->idPengguna === $pengguna->idPengguna;

        abort_unless($boleh, 403);

        $lokasi = $jenis === 'draft' ? $hasil->draftKonten : $hasil->fileInsight;

        abort_unless($lokasi && Storage::disk('local')->exists($lokasi), 404, 'Berkas tidak ditemukan.');

        // response() mengirim berkas untuk ditampilkan di browser (video diputar, gambar ditampilkan)
        return Storage::disk('local')->response($lokasi);
    }
}