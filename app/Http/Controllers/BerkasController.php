<?php

namespace App\Http\Controllers;

use App\Models\HasilPengerjaan;
use App\Models\AkunSosmed;
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

        return $this->kirimBerkas($lokasi);
    }

    // Membuka screenshot insight audiens (demografi). Admin atau influencer pemilik akun.
    public function demografi(Request $request, AkunSosmed $akun): Response
    {
        $pengguna = $request->user();

        $boleh = $pengguna->peran() === 'admin' || $akun->idPengguna === $pengguna->idPengguna;

        abort_unless($boleh, 403);

        return $this->kirimBerkas($akun->fileDemografi);
    }

    // response() mengirim berkas untuk ditampilkan di browser (video diputar, gambar ditampilkan)
    private function kirimBerkas(?string $lokasi): Response
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        abort_unless($lokasi && $disk->exists($lokasi), 404, 'Berkas tidak ditemukan.');

        return $disk->response($lokasi);
    }
}
