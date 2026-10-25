<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Feedback;
use App\Models\HasilPengerjaan;
use App\Models\AkunSosmed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;


// Use case Memeriksa Insight Campaign (Tabel 23). Wireframe Gambar 74.
class VerifikasiInsightController extends Controller
{
    private const PER_HALAMAN = 10;

    public function index(Request $request): View
    {
        $status = $request->query('status', 'menunggu');
        $idCampaign = $request->integer('campaign');

        // Hanya hasil yang insight-nya sudah pernah dikirim
        $query = HasilPengerjaan::with(['pendaftaran.campaign', 'pendaftaran.influencer.pengguna', 'akunSosmed'])
            ->where('statusInsight', '!=', 'belum_dikirim')
            ->when(in_array($status, ['menunggu', 'ditolak', 'disetujui'], true), fn ($q) => $q->where('statusInsight', $status))
            ->when($idCampaign, fn ($q) => $q->whereHas('pendaftaran', fn ($p) => $p->where('idCampaign', $idCampaign)))
            ->orderBy('tanggalPengirimanInsight')
            ->orderBy('idHasil');

        $daftar = (clone $query)->paginate(self::PER_HALAMAN)->withQueryString();

        $terpilih = $request->integer('pilih') ? (clone $query)->find($request->integer('pilih')) : null;
        $terpilih ??= $daftar->first();

        return view('admin.verifikasi-insight', [
            'daftar' => $daftar,
            'terpilih' => $terpilih,
            'pilihanCampaign' => Campaign::whereHas('pendaftaran.hasilPengerjaan', fn ($q) => $q->where('statusInsight', '!=', 'belum_dikirim'))
                ->orderBy('namaCampaign')->pluck('namaCampaign', 'idCampaign'),
            'filter' => ['status' => $status, 'campaign' => $idCampaign],
        ]);
    }

    // Tabel 23 langkah 6-7: insight sesuai bukti, status menjadi disetujui final
    public function setujui(HasilPengerjaan $hasil): RedirectResponse
    {
        // DB::transaction: dua perubahan di bawah harus berhasil bersama.
        // Kalau salah satu gagal, keduanya dibatalkan.
        $berhasil = DB::transaction(function () use ($hasil) {
            // Update bersyarat: hanya berubah kalau statusnya masih 'menunggu'.
            // Ini mencegah proses dobel (klik ganda atau dua tab).
            $jumlahBerubah = HasilPengerjaan::whereKey($hasil->idHasil)
                ->where('statusInsight', 'menunggu')
                ->update(['statusInsight' => 'disetujui']);

            if ($jumlahBerubah === 0) {
                return false;
            }

            // Perbarui jumlah pengikut di profil (tabel akunsosmed) memakai akun yang dipakai konten ini.
            // Dicek dulu tidak kosong, karena data lama bisa saja belum punya jumlahPengikut.
            if ($hasil->jumlahPengikut !== null) {
                AkunSosmed::whereKey($hasil->idAkun)
                    ->update(['jumlahPengikut' => $hasil->jumlahPengikut]);
            }

            return true;
        });

        if (! $berhasil) {
            return back()->with('gagal', 'Insight ini sudah diperiksa sebelumnya.');
        }

        return back()->with('sukses', 'Insight disetujui dan jumlah pengikut di profil influencer diperbarui. Pengerjaan ini siap diproses ke pembayaran.');
    }

    // Tabel 23 alternative scenario: angka tidak sesuai bukti, insight ditolak dengan alasan
    public function tolak(Request $request, HasilPengerjaan $hasil): RedirectResponse
    {
        $data = $request->validate(
            ['alasan' => ['required', 'string', 'max:2000']],
            [
                'alasan.required' => 'Tulis alasan penolakan supaya influencer tahu apa yang perlu diperbaiki.',
                'alasan.max' => 'Alasan maksimal 2000 karakter.',
            ]
        );

        $berhasil = DB::transaction(function () use ($hasil, $data, $request) {
            $jumlahBerubah = HasilPengerjaan::whereKey($hasil->idHasil)
                ->where('statusInsight', 'menunggu')
                ->update(['statusInsight' => 'ditolak']);

            if ($jumlahBerubah === 0) {
                return false;
            }

            // Alasan disimpan sebagai Feedback (Tabel 39: "catatan revisi atau evaluasi")
            Feedback::create([
                'idHasil' => $hasil->idHasil,
                'idPengguna' => $request->user()->idPengguna,
                'isiFeedback' => 'Insight ditolak: ' . $data['alasan'],
                'tanggalFeedback' => now()->toDateString(),
            ]);

            return true;
        });

        if (! $berhasil) {
            return back()->with('gagal', 'Insight ini sudah diperiksa sebelumnya.');
        }

        return back()->with('sukses', 'Insight ditolak. Influencer dapat melihat alasannya dan mengirim ulang.');
    }
}