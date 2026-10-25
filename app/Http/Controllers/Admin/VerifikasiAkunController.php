<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Influencer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Use case Verifikasi Akun (Tabel 8, Activity Diagram Gambar 16, Sequence Diagram Gambar 39)
class VerifikasiAkunController extends Controller
{
    private const PER_HALAMAN = 5;

    // Langkah 1-4 Tabel 8: daftar akun menunggu dan data influencer yang dipilih
    public function index(Request $request): View
    {
        // Pendaftar paling lama tampil paling atas, supaya tidak ada yang menunggu terlalu lama.
        // with('pengguna') mengambil nama dan email sekaligus (tidak query ulang per baris).
        $daftar = Influencer::with('pengguna')
            ->where('statusVerifikasi', 'menunggu')
            ->orderBy('idPengguna')
            ->paginate(self::PER_HALAMAN);

        // Influencer yang tampil di panel kanan: dari tautan "Periksa" (?pilih=ID),
        // atau baris pertama di halaman ini kalau belum memilih.
        $terpilih = null;

        if ($request->integer('pilih')) {
            $terpilih = Influencer::where('statusVerifikasi', 'menunggu')->find($request->integer('pilih'));
        }

        $terpilih ??= $daftar->first();
        $terpilih?->load(['pengguna', 'akunSosmed']);

        return view('admin.verifikasi-akun', [
            'daftar' => $daftar,
            'terpilih' => $terpilih,
        ]);
    }

    // Langkah 6-7 Tabel 8: Admin menyetujui
    public function setujui(Influencer $influencer): RedirectResponse
    {
        return $this->ubahStatus($influencer, 'diverifikasi', 'berhasil diverifikasi');
    }

    // Alternative scenario Tabel 8: data tidak memenuhi persyaratan
    public function tolak(Influencer $influencer): RedirectResponse
    {
        return $this->ubahStatus($influencer, 'ditolak', 'ditolak');
    }

    // Syarat statusVerifikasi = 'menunggu' membuat satu akun hanya bisa diproses sekali.
    // Kalau tombol terklik dua kali, atau halaman dibuka di dua tab, klik kedua tidak mengubah apa pun.
    private function ubahStatus(Influencer $influencer, string $statusBaru, string $keterangan): RedirectResponse
    {
        $jumlahBerubah = Influencer::whereKey($influencer->idPengguna)
            ->where('statusVerifikasi', 'menunggu')
            ->update(['statusVerifikasi' => $statusBaru]);

        if ($jumlahBerubah === 0) {
            return redirect()->route('admin.verifikasi')
                ->with('gagal', 'Akun ini sudah diproses sebelumnya.');
        }

        return redirect()->route('admin.verifikasi')
            ->with('sukses', "Akun {$influencer->pengguna->nama} {$keterangan}.");
    }
}