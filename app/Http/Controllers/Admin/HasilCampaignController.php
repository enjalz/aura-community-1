<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Feedback;
use App\Models\HasilPengerjaan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

// Use case Memeriksa Hasil Pengerjaan (Tabel 19), Memberikan Feedback (Tabel 20),
// dan Menyetujui Pengerjaan (Tabel 21). Wireframe Gambar 73.
class HasilCampaignController extends Controller
{
    private const PER_HALAMAN = 10;

    // Tabel 19 langkah 1-4: daftar hasil pengerjaan dan detail yang dipilih
    public function index(Request $request): View
    {
        // Bawaan: yang menunggu pemeriksaan, karena itu yang perlu ditindak Admin
        $status = $request->query('status', 'menunggu');
        $idCampaign = $request->integer('campaign');

        $query = HasilPengerjaan::with(['pendaftaran.campaign', 'pendaftaran.influencer.pengguna', 'akunSosmed'])
            ->when(array_key_exists($status, config('aura.statusDraft')), fn ($q) => $q->where('statusDraft', $status))
            ->when($idCampaign, fn ($q) => $q->whereHas('pendaftaran', fn ($p) => $p->where('idCampaign', $idCampaign)))
            ->orderBy('tanggalPengirimanDraft')
            ->orderBy('idHasil');

        $daftar = (clone $query)->paginate(self::PER_HALAMAN)->withQueryString();

        $terpilih = $request->integer('pilih') ? (clone $query)->find($request->integer('pilih')) : null;
        $terpilih ??= $daftar->first();
        $terpilih?->load(['feedback' => fn ($q) => $q->orderByDesc('idFeedback')]);

        return view('admin.hasil-campaign', [
            'daftar' => $daftar,
            'terpilih' => $terpilih,
            'pilihanCampaign' => Campaign::whereHas('pendaftaran.hasilPengerjaan')->orderBy('namaCampaign')->pluck('namaCampaign', 'idCampaign'),
            'filter' => ['status' => $status, 'campaign' => $idCampaign],
        ]);
    }

    // Tabel 20: menyimpan catatan revisi dan mengubah status draft menjadi revisi
    public function feedback(Request $request, HasilPengerjaan $hasil): RedirectResponse
    {
        $data = $request->validate(
            ['isiFeedback' => ['required', 'string', 'max:2000']],
            [
                'isiFeedback.required' => 'Tulis catatan revisi untuk influencer.',
                'isiFeedback.max' => 'Catatan maksimal 2000 karakter.',
            ]
        );

        // Status dan feedback disimpan bersama. Kalau draft ternyata sudah diproses, tidak ada yang disimpan.
        $berhasil = DB::transaction(function () use ($hasil, $data, $request) {
            $jumlahBerubah = HasilPengerjaan::whereKey($hasil->idHasil)
                ->where('statusDraft', 'menunggu')
                ->update(['statusDraft' => 'revisi']);

            if ($jumlahBerubah === 0) {
                return false;
            }

            Feedback::create([
                'idHasil' => $hasil->idHasil,
                'idPengguna' => $request->user()->idPengguna,
                'isiFeedback' => $data['isiFeedback'],
                'tanggalFeedback' => now()->toDateString(),
            ]);

            return true;
        });

        if (! $berhasil) {
            return back()->with('gagal', 'Draft ini sudah diperiksa sebelumnya.');
        }

        return back()->with('sukses', 'Feedback terkirim. Status draft berubah menjadi Revisi.');
    }

    // Tabel 21: menyetujui draft dan membuka form insight untuk influencer
    public function setujui(HasilPengerjaan $hasil): RedirectResponse
    {
        $jumlahBerubah = HasilPengerjaan::whereKey($hasil->idHasil)
            ->where('statusDraft', 'menunggu')
            ->update(['statusDraft' => 'disetujui']);

        if ($jumlahBerubah === 0) {
            return back()->with('gagal', 'Draft ini sudah diperiksa sebelumnya.');
        }

        return back()->with('sukses', 'Draft disetujui. Influencer dapat mengunggah konten dan mengirim insight.');
    }
}