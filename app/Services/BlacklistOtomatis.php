<?php

namespace App\Services;

use App\Models\Influencer;
use App\Models\PendaftaranCampaign;

// Blacklist otomatis untuk influencer yang diterima di campaign tetapi tidak menyelesaikan
// pengiriman konten sampai campaign berakhir (Tabel 11, alternative scenario tambahan).
// Hanya berlaku untuk campaign yang memiliki periode unggah.
class BlacklistOtomatis
{
    // Mengembalikan jumlah influencer yang baru dimasukkan ke blacklist
    public function jalankan(): int
    {
        $daftar = PendaftaranCampaign::query()
            ->where('statusPendaftaran', 'diterima')
            ->whereHas('campaign', fn ($q) => $q
                ->whereNotNull('tanggalUnggahSelesai')
                ->whereDate('tanggalSelesai', '<', today()))
            ->whereHas('influencer', fn ($q) => $q->where('statusBlacklist', false))
            ->with(['campaign.ketentuanPlatform', 'hasilPengerjaan'])
            ->get()
            ->filter(fn (PendaftaranCampaign $pendaftaran) => $this->tidakMenyelesaikan($pendaftaran));

        $jumlah = 0;

        foreach ($daftar as $pendaftaran) {
            $campaign = $pendaftaran->campaign;

            // where statusBlacklist = false: kalau satu influencer gagal di dua campaign, cukup tercatat sekali
            $jumlah += Influencer::whereKey($pendaftaran->idPengguna)
                ->where('statusBlacklist', false)
                ->update([
                    'statusBlacklist' => true,
                    'alasanBlacklist' => "Otomatis: tidak mengirim konten campaign {$campaign->namaCampaign} "
                        . "(periode unggah {$campaign->periodeUnggah()}) hingga campaign berakhir pada "
                        . $campaign->tanggalSelesai->format('d/m/Y') . '.',
                    'tanggalBlacklist' => today()->toDateString(),
                ]);
        }

        return $jumlah;
    }

    // Aturannya dibuat sejalan dengan syarat pembayaran di PembayaranController::syaratSiapDibayar(),
    // supaya influencer yang tidak bisa dibayar karena tidak menyelesaikan campaign juga ikut terdeteksi.
    private function tidakMenyelesaikan(PendaftaranCampaign $pendaftaran): bool
    {
        $hasil = $pendaftaran->hasilPengerjaan;
        $jumlahPlatform = $pendaftaran->campaign->ketentuanPlatform->pluck('platform')->unique()->count();

        // Tidak pernah mengirim draft, atau ada platform di ketentuan yang tidak dikerjakan sama sekali
        if ($hasil->isEmpty() || $hasil->count() < $jumlahPlatform) {
            return true;
        }

        // Masih ada draft yang belum diperiksa Admin: bukan kesalahan influencer
        if ($hasil->contains('statusDraft', 'menunggu')) {
            return false;
        }

        // Draft tidak diperbaiki, atau link dan insight tidak dikirim
        return $hasil->contains(fn ($h) => $h->statusDraft === 'revisi'
            || in_array($h->statusInsight, ['belum_dikirim', 'ditolak'], true));
    }
}