<?php

namespace App\Services;

use App\Models\HasilPengerjaan;
use App\Models\Notifikasi;

// Mengingatkan influencer saat masa tunggu insight (setelah link konten dikirim) sudah lewat.
// Dijalankan setiap influencer membuka halaman. Satu hasil pengerjaan hanya diingatkan sekali.
// Kalau notifikasinya dihapus padahal insight belum dikirim, pengingat muncul lagi.
class PengingatInsight
{
    public const JUDUL = 'Insight sudah dapat dikirim';

    public function jalankan(int $idInfluencer): void
    {
        $daftarHasil = HasilPengerjaan::with(['pendaftaran.campaign', 'akunSosmed'])
            ->whereHas('pendaftaran', fn ($q) => $q->where('idPengguna', $idInfluencer))
            ->whereNotNull('linkKonten')
            ->where('statusInsight', 'belum_dikirim')
            ->get()
            // Masa tunggu sudah lewat dan campaign belum berakhir (setelah berakhir, insight tidak bisa dikirim)
            ->filter(fn (HasilPengerjaan $hasil) => $hasil->bolehKirimInsight()
                && ! $hasil->pendaftaran->campaign->tanggalSelesai->lt(today()));

        foreach ($daftarHasil as $hasil) {
            $tautan = Notifikasi::tautanHasil($hasil);

            $sudahDiingatkan = Notifikasi::where('idPengguna', $idInfluencer)
                ->where('judul', self::JUDUL)
                ->where('tautan', $tautan)
                ->exists();

            if ($sudahDiingatkan) {
                continue;
            }

            $campaign = $hasil->pendaftaran->campaign;

            Notifikasi::kirim(
                $idInfluencer,
                self::JUDUL,
                "Masa tunggu {$campaign->namaCampaign} ({$hasil->akunSosmed->platform}) sudah lewat. "
                    . "Kirim tangkapan layar insight paling lambat {$campaign->tanggalSelesai->format('d/m/Y')}.",
                $tautan
            );
        }
    }
}