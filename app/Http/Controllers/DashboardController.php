<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Feedback;
use App\Models\HasilPengerjaan;
use App\Models\Influencer;
use App\Models\OwnerBrand;
use App\Models\Pembayaran;
use App\Services\RekapCampaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

// Use case Melihat Dashboard (Tabel 6) dan Melihat Status Verifikasi (Tabel 9)
class DashboardController extends Controller
{
    // Teks halaman Status Verifikasi (wireframe Gambar 79, 80, 81)
    private const STATUS = [
        'menunggu' => [
            'judul' => 'Akun Anda sedang menunggu verifikasi',
            'deskripsi' => 'Data registrasi Anda sedang diperiksa oleh Admin. Dashboard dan menu campaign dapat diakses setelah akun diverifikasi.',
            'label' => 'Menunggu Verifikasi',
            'ikon' => 'hourglass-split',
        ],
        'ditolak' => [
            'judul' => 'Verifikasi akun Anda ditolak',
            'deskripsi' => 'Data registrasi Anda tidak memenuhi persyaratan Aura Community.',
            'label' => 'Ditolak',
            'ikon' => 'x-circle',
        ],
        'diverifikasi' => [
            'judul' => 'Akun Anda telah diverifikasi',
            'deskripsi' => 'Anda dapat melihat dan mendaftar campaign yang tersedia.',
            'label' => 'Diverifikasi',
            'ikon' => 'check-circle',
        ],
    ];

    private const JUMLAH_BARIS = 5;   // jumlah baris tabel ringkas di dashboard

    // Pintu masuk setelah login: meneruskan ke dashboard sesuai peran (Tabel 6 langkah 2)
    public function arahkan(Request $request): RedirectResponse
    {
        $pengguna = $request->user();

        return match ($pengguna->peran()) {
            'admin' => redirect()->route('admin.dashboard'),
            'owner_brand' => redirect()->route('owner.dashboard'),
            'influencer' => redirect()->route(
                $pengguna->influencer->statusVerifikasi === 'diverifikasi' ? 'influencer.dashboard' : 'influencer.status'
            ),
            default => $this->tolakTanpaPeran($request),
        };
    }

    // Dashboard Admin (wireframe Gambar 68)
    public function admin(): View
    {
        // Jumlah campaign per status dalam satu query, contoh hasil: ['draft' => 2, 'berjalan' => 5]
        $campaignPerStatus = Campaign::query()
            ->toBase()
            ->selectRaw('statusCampaign, COUNT(*) AS jumlah')
            ->groupBy('statusCampaign')
            ->pluck('jumlah', 'statusCampaign');

        return view('dashboard.admin', [
            'ringkasanPengguna' => [
                'Total Influencer' => Influencer::where('statusVerifikasi', 'diverifikasi')->count(),
                'Menunggu Verifikasi' => Influencer::where('statusVerifikasi', 'menunggu')->count(),
                'Influencer Blacklist' => Influencer::where('statusBlacklist', true)->count(),
                'Total Owner Brand' => OwnerBrand::count(),
            ],
            'ringkasanCampaign' => [
                'Campaign Draft' => $campaignPerStatus['draft'] ?? 0,
                'Campaign Berjalan' => $campaignPerStatus['berjalan'] ?? 0,
                'Campaign Selesai' => $campaignPerStatus['selesai'] ?? 0,
            ],
            // Pekerjaan yang menunggu tindakan Admin, beserta tautan ke halamannya
            'perluDitindak' => [
                ['label' => 'Draft menunggu pemeriksaan', 'jumlah' => HasilPengerjaan::where('statusDraft', 'menunggu')->count(), 'route' => 'admin.hasil'],
                ['label' => 'Insight menunggu pemeriksaan', 'jumlah' => HasilPengerjaan::where('statusInsight', 'menunggu')->count(), 'route' => 'admin.insight'],
                ['label' => 'Pembayaran pending', 'jumlah' => Pembayaran::where('statusPembayaran', 'pending')->count(), 'route' => 'admin.pembayaran'],
            ],
            'campaignBerjalan' => Campaign::where('statusCampaign', 'berjalan')->orderBy('tanggalSelesai')->limit(self::JUMLAH_BARIS)->get(),
        ]);
    }

    // Dashboard Owner Brand (wireframe Gambar 87). Hanya campaign yang diberi akses.
    public function ownerBrand(Request $request, RekapCampaign $rekap): View
    {
        $ownerBrand = $request->user()->ownerBrand;
        $laporanFinal = fn ($q) => $q->where('statusLaporan', 'final');

        return view('dashboard.owner-brand', [
            'ringkasan' => [
                'Campaign Berjalan' => $ownerBrand->campaign()->where('statusCampaign', 'berjalan')->count(),
                'Campaign Selesai' => $ownerBrand->campaign()->where('statusCampaign', 'selesai')->count(),
                'Laporan Tersedia' => $ownerBrand->campaign()->whereHas('laporan', $laporanFinal)->count(),
            ],
            'campaignBerjalan' => $ownerBrand->campaign()->where('statusCampaign', 'berjalan')->orderBy('tanggalSelesai')->limit(self::JUMLAH_BARIS)->get(),
            // Setiap laporan disertai ringkasan statistik dari RekapCampaign (Modul 7A)
            'laporan' => $ownerBrand->campaign()
                ->whereHas('laporan', $laporanFinal)
                ->orderByDesc('tanggalSelesai')
                ->limit(self::JUMLAH_BARIS)
                ->get()
                ->map(fn (Campaign $campaign) => ['campaign' => $campaign, 'ringkasan' => $rekap->ringkasan($campaign)]),
        ]);
    }

    // Dashboard Influencer (wireframe Gambar 82). Hanya untuk influencer terverifikasi (middleware terverifikasi).
    public function influencer(Request $request): View
    {
        $influencer = $request->user()->influencer;
        $milikSaya = fn ($q) => $q->where('idPengguna', $influencer->idPengguna);
        $hasilSaya = HasilPengerjaan::whereHas('pendaftaran', $milikSaya);

        // Campaign yang sudah didaftar tidak ditampilkan lagi di "Campaign Tersedia"
        $idTerdaftar = $influencer->pendaftaran()->pluck('idCampaign');

        return view('dashboard.influencer', [
            'ringkasan' => [
                'Campaign Diikuti' => $influencer->pendaftaran()->where('statusPendaftaran', 'diterima')->count(),
                'Draft Perlu Revisi' => (clone $hasilSaya)->where('statusDraft', 'revisi')->count(),
                'Insight Belum Dikirim' => (clone $hasilSaya)->where('statusDraft', 'disetujui')->whereIn('statusInsight', ['belum_dikirim', 'ditolak'])->count(),
                'Pembayaran Pending' => Pembayaran::where('statusPembayaran', 'pending')->whereHas('pendaftaran', $milikSaya)->count(),
            ],
            'campaignSaya' => $this->campaignSaya($influencer),
            'feedbackTerbaru' => Feedback::whereHas('hasilPengerjaan.pendaftaran', $milikSaya)
                ->with('hasilPengerjaan.pendaftaran.campaign')
                ->orderByDesc('idFeedback')
                ->limit(self::JUMLAH_BARIS)
                ->get(),
            'campaignTersedia' => Campaign::where('statusCampaign', 'berjalan')
                ->where('kategoriInfluencer', $influencer->kategori)
                ->whereDate('tanggalSelesai', '>=', now()->toDateString())
                ->whereNotIn('idCampaign', $idTerdaftar)
                ->orderBy('tanggalSelesai')
                ->limit(self::JUMLAH_BARIS)
                ->get(),
        ]);
    }

    // Use case Melihat Status Verifikasi (Tabel 9)
    public function statusVerifikasi(Request $request): View
    {
        $pengguna = $request->user();
        $influencer = $pengguna->influencer;

        return view('dashboard.status-verifikasi', [
            'pengguna' => $pengguna,
            'influencer' => $influencer,
            'akunSosmed' => $influencer->akunSosmed->keyBy('platform'),
            'status' => self::STATUS[$influencer->statusVerifikasi],
        ]);
    }

    // Tabel "Campaign Saya" di dashboard influencer.
    // Satu campaign bisa punya beberapa hasil pengerjaan (satu per platform), jadi yang ditampilkan
    // adalah status yang paling butuh perhatian. Contoh: kalau ada satu draft Revisi, yang tampil Revisi.
    private function campaignSaya(Influencer $influencer): Collection
    {
        return $influencer->pendaftaran()
            ->with(['campaign', 'hasilPengerjaan', 'pembayaran'])
            ->orderByDesc('idPendaftaran')
            ->limit(self::JUMLAH_BARIS)
            ->get()
            ->map(fn ($pendaftaran) => (object) [
                'pendaftaran' => $pendaftaran,
                'statusDraft' => $this->statusUtama($pendaftaran->hasilPengerjaan->pluck('statusDraft'), ['revisi', 'menunggu', 'disetujui']),
                'statusInsight' => $this->statusUtama($pendaftaran->hasilPengerjaan->pluck('statusInsight'), ['ditolak', 'belum_dikirim', 'menunggu', 'disetujui']),
            ]);
    }

    // Mengambil status pertama (menurut urutan prioritas) yang ada di daftar. Null kalau daftar kosong.
    private function statusUtama(Collection $daftarStatus, array $urutanPrioritas): ?string
    {
        foreach ($urutanPrioritas as $status) {
            if ($daftarStatus->contains($status)) {
                return $status;
            }
        }

        return null;
    }

    private function tolakTanpaPeran(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->withErrors(['email' => 'Akun Anda belum memiliki peran. Silakan hubungi Admin.']);
    }
}