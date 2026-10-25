<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\LaporanCampaign;
use App\Services\RekapCampaign;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Use case Laporan Hasil Campaign untuk Admin (Tabel 25).
// Wireframe Gambar 76 (daftar) dan Gambar 77 (detail).
class LaporanController extends Controller
{
    private const PER_HALAMAN = 10;

    // Daftar campaign selesai (Gambar 76). Detail dibuka lewat tombol Pilih.
    public function index(Request $request, RekapCampaign $rekap): View
    {
        $cari   = trim((string) $request->query('cari'));
        $status = $request->query('status');

        $daftar = $this->campaignSelesai()
            ->when($cari !== '', fn ($q) => $q->where('namaCampaign', 'like', '%' . addcslashes($cari, '%_\\') . '%'))
            ->when($status === 'belum', fn ($q) => $q->doesntHave('laporan'))
            ->when(in_array($status, ['draft', 'final'], true), fn ($q) => $q->whereHas('laporan', fn ($l) => $l->where('statusLaporan', $status)))
            ->orderByDesc('tanggalSelesai')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        // Total views per campaign untuk kolom "Total Views", dikunci dengan idCampaign
        $ringkasan = $daftar->getCollection()
            ->mapWithKeys(fn (Campaign $campaign) => [$campaign->idCampaign => $rekap->ringkasan($campaign)]);

        return view('admin.laporan', [
            'daftar'    => $daftar,
            'ringkasan' => $ringkasan,
            'filter'    => compact('cari', 'status'),
        ]);
    }

    // Detail laporan satu campaign (Gambar 77, Tabel 25 langkah 3-4)
    public function detail(Campaign $campaign, RekapCampaign $rekap): View
    {
        // Entry condition Tabel 25: laporan hanya untuk campaign yang sudah selesai
        abort_unless($campaign->statusCampaign === 'selesai', 404);

        $campaign->load(['laporan', 'ketentuanPlatform']);

        return view('admin.laporan-detail', [
            'campaign'  => $campaign,
            'laporan'   => $campaign->laporan,
            'ringkasan' => $rekap->ringkasan($campaign),
            'performa'  => $rekap->performa($campaign), // ER, view rate, label performa (Bab 2.1.4)
            'rincian'   => $rekap->rincian($campaign),
        ]);
    }

    // Tabel 25 langkah 4-5: menyimpan catatan evaluasi, sebagai draft atau final (dipublikasikan ke Owner Brand)
    public function simpan(Request $request, Campaign $campaign): RedirectResponse
    {
        // Entry condition Tabel 25: campaign telah selesai
        if ($campaign->statusCampaign !== 'selesai') {
            return back()->with('gagal', 'Laporan hanya dapat dibuat untuk campaign yang sudah selesai.');
        }

        if ($campaign->laporan?->statusLaporan === 'final') {
            return back()->with('gagal', 'Laporan ini sudah difinalisasi dan dipublikasikan.');
        }

        $aksi = $request->input('aksi');

        $data = $request->validate(
            [
                'aksi' => ['required', Rule::in(['draft', 'final'])],
                // Draft boleh tanpa catatan. Finalisasi wajib ada catatan evaluasi.
                'catatanEvaluasi' => [$aksi === 'final' ? 'required' : 'nullable', 'string', 'max:5000'],
            ],
            [
                'catatanEvaluasi.required' => 'Tulis catatan evaluasi sebelum laporan dipublikasikan.',
                'catatanEvaluasi.max'      => 'Catatan evaluasi maksimal 5000 karakter.',
            ]
        );

        // updateOrCreate: satu campaign paling banyak satu laporan (idCampaign UNIQUE, Tabel 36)
        LaporanCampaign::updateOrCreate(
            ['idCampaign' => $campaign->idCampaign],
            [
                'idPengguna'             => $request->user()->idPengguna,
                'catatanEvaluasi'        => $data['catatanEvaluasi'] ?? '', // kolom NOT NULL, jadi diisi teks kosong kalau belum ada
                'tanggalSelesaiCampaign' => now()->toDateString(),           // Tabel 36: tanggal finalisasi laporan
                'statusLaporan'          => $data['aksi'],
            ]
        );

        $pesan = $data['aksi'] === 'final'
            ? 'Laporan difinalisasi dan dipublikasikan. Owner Brand terkait sekarang dapat melihatnya.'
            : 'Draft laporan disimpan.';

        // Kembali ke halaman detail laporan yang sama
        return redirect()->route('admin.laporan.detail', $campaign)->with('sukses', $pesan);
    }

    // Campaign selesai, beserta laporannya dan jumlah influencer yang diterima
    private function campaignSelesai(): Builder
    {
        return Campaign::query()
            ->where('statusCampaign', 'selesai')
            ->with('laporan')
            ->withCount(['pendaftaran as jumlahInfluencer' => fn ($q) => $q->where('statusPendaftaran', 'diterima')]);
    }
}