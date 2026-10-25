<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\PendaftaranCampaign;
use Illuminate\Http\RedirectResponse;

// Pemilihan influencer pendaftar campaign (Batasan 7: "pemilihan Influencer").
// Influencer yang diterima dapat mengirim hasil pengerjaan (syarat Tabel 18).
class PendaftarCampaignController extends Controller
{
    public function terima(Campaign $campaign, PendaftaranCampaign $pendaftaran): RedirectResponse
    {
        $this->pastikanMilikCampaign($campaign, $pendaftaran);

        if ($pendaftaran->influencer->statusBlacklist) {
            return back()->with('gagal', 'Influencer ini berstatus blacklist, sehingga tidak dapat diterima.');
        }

        $jumlahDiterima = $campaign->pendaftaran()->where('statusPendaftaran', 'diterima')->count();

        if ($jumlahDiterima >= $campaign->kuotaInfluencer) {
            return back()->with('gagal', "Kuota campaign sudah penuh ({$campaign->kuotaInfluencer} influencer).");
        }

        return $this->ubahStatus($campaign, $pendaftaran, 'diterima');
    }

    public function tolak(Campaign $campaign, PendaftaranCampaign $pendaftaran): RedirectResponse
    {
        $this->pastikanMilikCampaign($campaign, $pendaftaran);

        return $this->ubahStatus($campaign, $pendaftaran, 'ditolak');
    }

    // Mencegah URL yang dimanipulasi: pendaftaran milik campaign lain tidak boleh diproses dari sini
    private function pastikanMilikCampaign(Campaign $campaign, PendaftaranCampaign $pendaftaran): void
    {
        abort_unless($pendaftaran->idCampaign === $campaign->idCampaign, 404);
    }

    // Hanya pendaftaran yang masih menunggu yang bisa diubah (mencegah klik dobel)
    private function ubahStatus(Campaign $campaign, PendaftaranCampaign $pendaftaran, string $statusBaru): RedirectResponse
    {
        $jumlahBerubah = PendaftaranCampaign::whereKey($pendaftaran->idPendaftaran)
            ->where('statusPendaftaran', 'menunggu')
            ->update(['statusPendaftaran' => $statusBaru]);

        if ($jumlahBerubah === 0) {
            return back()->with('gagal', 'Pendaftaran ini sudah diproses sebelumnya.');
        }

        $nama = $pendaftaran->influencer->pengguna->nama;

        return redirect()->route('admin.campaign.detail', $campaign)
            ->with('sukses', "Pendaftaran {$nama} {$statusBaru}.");
    }
}