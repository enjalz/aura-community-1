<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Notifikasi;
use App\Models\Pembayaran;
use App\Models\PendaftaranCampaign;
use App\Models\RekeningInfluencer;
use App\Services\XenditPayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

// Use case Memproses Pembayaran Campaign (Tabel 24). Wireframe Gambar 75.
class PembayaranController extends Controller
{
    private const PER_HALAMAN = 10;

    public function index(Request $request): View
    {
        $cari = trim((string) $request->query('cari'));
        $idCampaign = $request->integer('campaign');
        $status = $request->query('status');

        $query = $this->queryDaftar()
            ->when($cari !== '', fn ($q) => $q->whereHas('influencer.pengguna', fn ($p) => $p->where('nama', 'like', '%' . addcslashes($cari, '%_\\') . '%')))
            ->when($idCampaign, fn ($q) => $q->where('idCampaign', $idCampaign))
            ->when($status === 'belum', fn ($q) => $q->doesntHave('pembayaran'))
            ->when(in_array($status, ['pending', 'berhasil', 'gagal'], true), fn ($q) => $q->whereHas('pembayaran', fn ($p) => $p->where('statusPembayaran', $status)))
            ->orderByDesc('PendaftaranCampaign.idPendaftaran');

        $daftar = (clone $query)->paginate(self::PER_HALAMAN)->withQueryString();

        $terpilih = $request->integer('pilih') ? (clone $query)->find($request->integer('pilih')) : null;

        return view('admin.pembayaran', [
            'daftar' => $daftar,
            'terpilih' => $terpilih,
            'pilihanCampaign' => Campaign::whereHas('pendaftaran', fn ($q) => $q->where('statusPendaftaran', 'diterima'))
                ->orderBy('namaCampaign')->pluck('namaCampaign', 'idCampaign'),
            'filter' => compact('cari', 'status') + ['campaign' => $idCampaign],
        ]);
    }

    // Tabel 24 langkah 3-5: memproses pembayaran lewat Xendit dan menyimpan statusnya
    public function proses(Request $request, PendaftaranCampaign $pendaftaran, XenditPayout $xendit): RedirectResponse
    {
        $siapDibayar = $this->syaratSiapDibayar(
            PendaftaranCampaign::whereKey($pendaftaran->idPendaftaran)->where('statusPendaftaran', 'diterima')
        )->exists();

        if (! $siapDibayar) {
            return back()->with('gagal', 'Pembayaran baru dapat diproses setelah seluruh hasil pengerjaan dan insight disetujui.');
        }

        $pendaftaran->load(['campaign', 'influencer.rekening', 'pembayaran']);
        $rekening = $pendaftaran->influencer->rekening;

        if (! $rekening) {
            return back()->with('gagal', 'Influencer belum mengisi data rekening.');
        }

        $pembayaran = $pendaftaran->pembayaran;

        // Yang sedang pending atau sudah berhasil tidak boleh dikirim lagi (mencegah transfer dobel)
        if ($pembayaran && in_array($pembayaran->statusPembayaran, ['pending', 'berhasil'], true)) {
            return back()->with('gagal', 'Pembayaran ini sudah diproses. Gunakan Cek Status untuk melihat hasilnya.');
        }

        // Rekening sebelumnya bermasalah: tunggu influencer menggantinya, supaya tidak gagal lagi ke rekening yang sama
        if ($pembayaran?->menungguRekeningBaru($rekening)) {
            return back()->with('gagal', 'Influencer belum memperbarui rekening. Pembayaran dapat diproses ulang setelah rekening diganti.');
        }

        // Baris Pembayaran disimpan dulu (status pending) supaya ada idPembayaran untuk referensi ke Xendit.
        // Kalau sebelumnya gagal, baris yang sama dipakai ulang (idPendaftaran di tabel Pembayaran bersifat UNIQUE).
        $isian = [
            'idPengguna' => $request->user()->idPengguna,
            'idRekening' => $rekening->idRekening,
            'jumlahPembayaran' => $pendaftaran->campaign->feePerInfluencer,
            'tanggalPembayaran' => now()->toDateString(),
            'statusPembayaran' => 'pending',
            'idTransaksiGateway' => null,
            'waktuKonfirmasi' => null,
            // Catatan kegagalan sebelumnya dikosongkan karena pembayaran diproses ulang
            'kodeGagal' => null,
            'rekeningGagal' => null,
        ];

        if ($pembayaran) {
            $pembayaran->update($isian);
        } else {
            $pembayaran = Pembayaran::create($isian + ['idPendaftaran' => $pendaftaran->idPendaftaran]);
        }

        $hasil = $xendit->buatPayout($pembayaran, $rekening);

        $pembayaran->update([
            'statusPembayaran' => $hasil['status'],
            'idTransaksiGateway' => $hasil['id'],
            'waktuKonfirmasi' => $hasil['status'] === 'pending' ? null : now(),
        ]);

        $this->tindakLanjut($pembayaran, $hasil, $rekening, $xendit);

        // Tabel 24 alternative scenario: transfer gagal, status tidak diubah menjadi berhasil
        if ($hasil['status'] === 'gagal') {
            return back()->with('gagal', $this->pesanGagal($pembayaran, $hasil));
        }

        return redirect()->route('admin.pembayaran')->with('sukses', 'Pembayaran dikirim ke Xendit dan berstatus Pending. Klik Cek Status untuk melihat hasil akhirnya.');
    }

    // Menanyakan status akhir ke Xendit (pengganti webhook untuk server lokal)
    public function cekStatus(Pembayaran $pembayaran, XenditPayout $xendit): RedirectResponse
    {
        if ($pembayaran->statusPembayaran !== 'pending' || ! $pembayaran->idTransaksiGateway) {
            return back()->with('gagal', 'Hanya pembayaran berstatus Pending yang dapat dicek.');
        }

        $hasil = $xendit->cekStatus($pembayaran->idTransaksiGateway);

        // Gagal menghubungi Xendit bukan berarti pembayarannya gagal, jadi status tidak diubah
        if ($hasil['id'] === null) {
            return back()->with('gagal', 'Status belum dapat dicek: ' . $hasil['pesan']);
        }

        $pembayaran->update([
            'statusPembayaran' => $hasil['status'],
            // Waktu dari Xendit dalam UTC, diubah ke zona waktu aplikasi (APP_TIMEZONE)
            'waktuKonfirmasi' => $hasil['status'] !== 'pending' && $hasil['diperbarui']
                ? Carbon::parse($hasil['diperbarui'])->timezone(config('app.timezone'))
                : null,
        ]);

        $this->tindakLanjut($pembayaran, $hasil, $pembayaran->pendaftaran->influencer->rekening, $xendit);

        // Berhasil: kembali ke tabel supaya Admin bisa memproses data berikutnya.
        // Gagal atau masih pending: tetap di detail, supaya Admin bisa proses ulang atau cek lagi.
        return match ($hasil['status']) {
            'berhasil' => redirect()->route('admin.pembayaran')->with('sukses', 'Pembayaran berhasil diterima rekening influencer.'),
            'gagal' => back()->with('gagal', $this->pesanGagal($pembayaran, $hasil)),
            default => back()->with('sukses', 'Pembayaran masih diproses bank. Coba cek lagi beberapa saat lagi.'),
        };
    }

    // Setelah status akhir diketahui: kirim notifikasi ke influencer.
    // Gagal karena rekening: simpan rekening yang bermasalah dan minta influencer menggantinya.
    // Gagal karena hal lain (saldo Xendit, koneksi): tidak dikabarkan ke influencer, cukup Admin yang proses ulang.
    private function tindakLanjut(Pembayaran $pembayaran, array $hasil, ?RekeningInfluencer $rekening, XenditPayout $xendit): void
    {
        $pendaftaran = $pembayaran->pendaftaran;
        $namaCampaign = $pendaftaran->campaign->namaCampaign;

        if ($hasil['status'] === 'berhasil') {
            Notifikasi::kirim(
                $pendaftaran->idPengguna,
                'Pembayaran fee berhasil',
                "Fee campaign {$namaCampaign} sebesar Rp " . number_format((float) $pembayaran->jumlahPembayaran, 0, ',', '.')
                    . ' sudah dikirim ke rekening Anda.',
                route('influencer.riwayat', [], false)
            );

            return;
        }

        if ($hasil['status'] !== 'gagal') {
            return;
        }

        $kode = $hasil['kode'] ?? null;
        $masalahRekening = $xendit->masalahRekening($kode);

        $pembayaran->update([
            'kodeGagal' => $kode !== null ? substr($kode, 0, 50) : null,
            'rekeningGagal' => $masalahRekening ? Pembayaran::ringkasRekening($rekening) : null,
        ]);

        if ($masalahRekening) {
            Notifikasi::kirim(
                $pendaftaran->idPengguna,
                'Pembayaran fee gagal, periksa rekening Anda',
                "Fee campaign {$namaCampaign} tidak dapat dikirim. " . $xendit->alasanRekening($kode)
                    . ' Harap cek ulang dan perbarui data rekening di Profil supaya pembayaran dapat diproses ulang.',
                route('influencer.profil', [], false) . '#rekening'
            );
        }
    }

        private function pesanGagal(Pembayaran $pembayaran, array $hasil): string
    {
        // Kode dari Xendit diterjemahkan lewat config aura.penyebabGagal, contoh INVALID_DESTINATION => "Rekening tidak valid"
        $kode = $hasil['kode'] ?? null;
        $penyebab = $kode ? config("aura.penyebabGagal.{$kode}", 'Transfer gagal diproses') : $hasil['pesan'];

        $teks = 'Pembayaran gagal' . ($penyebab ? ": {$penyebab}" : '') . '.';

        return $pembayaran->rekeningGagal !== null
            ? $teks . ' Notifikasi sudah dikirim ke influencer untuk memperbarui rekening.'
            : $teks . ' Periksa kendalanya, lalu proses ulang.';
    }
    // Pendaftaran yang tampil: diterima, dan sudah punya data pembayaran atau sudah siap dibayar
    private function queryDaftar(): Builder
    {
        return PendaftaranCampaign::query()
            ->select('PendaftaranCampaign.*')
            // Jumlah platform yang wajib dikerjakan (dari Ketentuan Platform campaign)
            ->selectRaw('(SELECT COUNT(DISTINCT k.platform) FROM KetentuanPlatform k WHERE k.idCampaign = PendaftaranCampaign.idCampaign) AS jumlahPlatform')
            ->withCount(['hasilPengerjaan as jumlahDisetujui' => fn ($q) => $q->where('statusInsight', 'disetujui')])
            ->with(['campaign', 'influencer.pengguna', 'influencer.rekening', 'pembayaran'])
            ->where('statusPendaftaran', 'diterima')
            ->where(fn ($q) => $q->has('pembayaran')->orWhere(fn ($q) => $this->syaratSiapDibayar($q)));
    }

    // Catatan Class Diagram: "Pembayaran dilakukan jika seluruh HasilPengerjaan disetujui".
    // Artinya: ada hasil untuk setiap platform di ketentuan, dan tidak ada insight yang belum disetujui.
    public static function syaratSiapDibayar(Builder $query): Builder
    {
        return $query
            ->whereHas('hasilPengerjaan')
            ->whereDoesntHave('hasilPengerjaan', fn ($q) => $q->where('statusInsight', '!=', 'disetujui'))
            ->whereRaw('(SELECT COUNT(*) FROM HasilPengerjaan h WHERE h.idPendaftaran = PendaftaranCampaign.idPendaftaran)
                        >= (SELECT COUNT(DISTINCT k.platform) FROM KetentuanPlatform k WHERE k.idCampaign = PendaftaranCampaign.idCampaign)');
    }
}