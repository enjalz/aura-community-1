<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CampaignRequest;
use App\Models\Campaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

// Use case Mengelola Campaign (Tabel 14)
class CampaignController extends Controller
{
    private const PER_HALAMAN = 10;

    // Daftar campaign (wireframe Gambar 72)
    public function index(Request $request): View
    {
        $cari = trim((string) $request->query('cari'));
        $status = $request->query('status');

        $daftar = Campaign::query()
            ->when($cari !== '', fn ($q) => $q->where('namaCampaign', 'like', '%' . addcslashes($cari, '%_\\') . '%'))
            ->when(array_key_exists((string) $status, config('aura.statusCampaign')), fn ($q) => $q->where('statusCampaign', $status))
            ->orderByDesc('tanggalMulai')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('admin.campaign.index', [
            'daftar' => $daftar,
            'filter' => compact('cari', 'status'),
        ]);
    }

    public function create(): View
    {
        return view('admin.campaign.form', ['campaign' => null]);
    }

    // Menyimpan campaign beserta ketentuan platformnya
    public function store(CampaignRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $campaign = DB::transaction(function () use ($data, $request) {
            $campaign = Campaign::create([
                ...Arr::except($data, 'ketentuan'),
                'idPengguna' => $request->user()->idPengguna,   // admin yang membuat (FK ke tabel Admin)
            ]);

            $campaign->ketentuanPlatform()->createMany($data['ketentuan']);

            return $campaign;
        });

        return redirect()->route('admin.campaign.detail', $campaign)
            ->with('sukses', 'Campaign berhasil dibuat. Berikan akses ke Owner Brand terkait melalui tombol Akses.');
    }

    // Detail campaign, ketentuan, owner brand yang punya akses, dan daftar pendaftar
    public function show(Campaign $campaign): View
    {
        $campaign->load([
            'ketentuanPlatform',
            'ownerBrand',
            'pendaftaran' => fn ($q) => $q->orderByRaw("FIELD(statusPendaftaran, 'menunggu', 'diterima', 'ditolak')")->orderBy('tanggalPendaftaran'),
            'pendaftaran.influencer.pengguna',
            'pendaftaran.influencer.akunSosmed',
        ]);

        return view('admin.campaign.detail', [
            'campaign' => $campaign,
            'jumlahDiterima' => $campaign->pendaftaran->where('statusPendaftaran', 'diterima')->count(),
        ]);
    }

    public function edit(Campaign $campaign): View
    {
        $campaign->load('ketentuanPlatform');

        return view('admin.campaign.form', ['campaign' => $campaign]);
    }

    // Ketentuan lama dihapus lalu dibuat ulang dari isi form.
    // Aman dilakukan karena tidak ada tabel lain yang merujuk ke KetentuanPlatform.
    public function update(CampaignRequest $request, Campaign $campaign): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $campaign) {
            $campaign->update(Arr::except($data, 'ketentuan'));
            $campaign->ketentuanPlatform()->delete();
            $campaign->ketentuanPlatform()->createMany($data['ketentuan']);
        });

        return redirect()->route('admin.campaign.detail', $campaign)
            ->with('sukses', 'Campaign berhasil diperbarui.');
    }

    // Campaign yang sudah punya pendaftar tidak dihapus,
    // supaya riwayat pendaftaran, hasil pengerjaan, dan pembayaran tetap ada.
    public function destroy(Campaign $campaign): RedirectResponse
    {
        if ($campaign->pendaftaran()->exists()) {
            return back()->with('gagal', 'Campaign ini sudah memiliki pendaftar, sehingga tidak dapat dihapus. Ubah statusnya menjadi Selesai bila campaign sudah berakhir.');
        }

        $nama = $campaign->namaCampaign;
        $campaign->delete();   // ketentuan dan akses ikut terhapus (ON DELETE CASCADE)

        return redirect()->route('admin.campaign')->with('sukses', "{$nama} berhasil dihapus.");
    }
}