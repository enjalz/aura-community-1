<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Pembayaran;
use App\Models\PendaftaranCampaign;
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
        $terpilih ??= $daftar->first();

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

        // Tabel 24 alternative scenario: transfer gagal, status tidak diubah menjadi berhasil
        if ($hasil['status'] === 'gagal') {
            return back()->with('gagal', 'Pembayaran gagal: ' . $hasil['pesan']);
        }

        return back()->with('sukses', 'Pembayaran dikirim ke Xendit dan berstatus Pending. Klik Cek Status untuk melihat hasil akhirnya.');
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

        return match ($hasil['status']) {
            'berhasil' => back()->with('sukses', 'Pembayaran berhasil diterima rekening influencer.'),
            'gagal' => back()->with('gagal', 'Pembayaran gagal' . ($hasil['pesan'] ? " ({$hasil['pesan']})" : '') . '. Periksa rekening influencer, lalu proses ulang.'),
            default => back()->with('sukses', 'Pembayaran masih diproses bank. Coba cek lagi beberapa saat lagi.'),
        };
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
    private function syaratSiapDibayar(Builder $query): Builder
    {
        return $query
            ->whereHas('hasilPengerjaan')
            ->whereDoesntHave('hasilPengerjaan', fn ($q) => $q->where('statusInsight', '!=', 'disetujui'))
            ->whereRaw('(SELECT COUNT(*) FROM HasilPengerjaan h WHERE h.idPendaftaran = PendaftaranCampaign.idPendaftaran)
                        >= (SELECT COUNT(DISTINCT k.platform) FROM KetentuanPlatform k WHERE k.idCampaign = PendaftaranCampaign.idCampaign)');
    }
}