<?php

namespace App\Http\Controllers\Influencer;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Influencer;
use App\Models\PendaftaranCampaign;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Use case Melihat Detail Campaign (Tabel 16) dan Mendaftar Campaign (Tabel 17) untuk Influencer
class CampaignController extends Controller
{
    private const PER_HALAMAN = 10;

    // Daftar campaign dan panel detail campaign yang dipilih (wireframe Gambar 83)
    public function index(Request $request): View
    {
        $influencer = $request->user()->influencer->load('rekening');
        $cari = trim((string) $request->query('cari'));
        $kategori = $request->query('kategori');

        $daftar = $this->campaignTerlihat($influencer)
            ->when($cari !== '', fn ($q) => $q->where('namaCampaign', 'like', '%' . addcslashes($cari, '%_\\') . '%'))
            ->when(in_array($kategori, config('aura.kategori'), true), fn ($q) => $q->where('kategoriInfluencer', $kategori))
            ->orderBy('tanggalSelesai')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        // Campaign yang ditampilkan di panel detail: dari tautan "Pilih", atau baris pertama.
        // campaignTerlihat() memastikan influencer tidak bisa membuka campaign draft lewat URL.
        $terpilih = $request->integer('pilih')
            ? $this->campaignTerlihat($influencer)->find($request->integer('pilih'))
            : null;
        $terpilih ??= $daftar->first();
        $terpilih?->load('ketentuanPlatform');

        // Pendaftaran milik influencer ini, dikelompokkan per idCampaign untuk kolom Status Pendaftaran
        $pendaftaranSaya = $influencer->pendaftaran()->get()->keyBy('idCampaign');

        return view('influencer.campaign', [
            'daftar' => $daftar,
            'terpilih' => $terpilih,
            'influencer' => $influencer,
            'pendaftaranSaya' => $pendaftaranSaya,
            'pendaftaranTerpilih' => $terpilih ? $pendaftaranSaya->get($terpilih->idCampaign) : null,
            'alasanTidakBisaDaftar' => $terpilih ? $this->alasanTidakBisaDaftar($influencer, $terpilih) : null,
            'filter' => compact('cari', 'kategori'),
        ]);
    }

    // Tabel 17, langkah 4-6: influencer mendaftar, sistem memvalidasi kriteria, lalu menyimpan pendaftaran
    public function daftar(Request $request, Campaign $campaign): RedirectResponse
    {
        $influencer = $request->user()->influencer->load('rekening');

        // Alternative scenario Tabel 17: blacklist atau tidak memenuhi ketentuan, pendaftaran ditolak
        if ($alasan = $this->alasanTidakBisaDaftar($influencer, $campaign)) {
            return back()->with('gagal', $alasan);
        }

        // Rekening wajib diisi saat mendaftar kalau influencer belum punya (catatan wireframe Gambar 83)
        $rekeningBaru = null;

        if (! $influencer->rekening) {
            $rekeningBaru = $request->validate(
                [
                    'jenisRekening' => ['required', Rule::in(['bank', 'ewallet'])],
                    'namaPenyedia' => ['required', Rule::in(collect(config('aura.kanalPembayaran'))->flatMap(fn ($daftar) => array_keys($daftar))->all())],
                    'nomorRekening' => ['required', 'regex:/^[0-9]{5,30}$/'],
                    'namaPemilik' => ['required', 'string', 'max:100'],
                ],
                [
                    'required' => ':attribute wajib diisi.',
                    'in' => 'Pilihan :attribute tidak valid.',
                    'max' => ':attribute maksimal :max karakter.',
                    'nomorRekening.regex' => 'Nomor Rekening hanya berisi angka, 5 sampai 30 digit.',
                ],
                [
                    'jenisRekening' => 'Jenis Rekening',
                    'namaPenyedia' => 'Nama Penyedia',
                    'nomorRekening' => 'Nomor Rekening',
                    'namaPemilik' => 'Nama Pemilik',
                ]
            );
        }

        DB::transaction(function () use ($influencer, $campaign, $rekeningBaru) {
            if ($rekeningBaru) {
                $influencer->rekening()->create($rekeningBaru);
            }

            PendaftaranCampaign::create([
                'idCampaign' => $campaign->idCampaign,
                'idPengguna' => $influencer->idPengguna,
                'tanggalPendaftaran' => now()->toDateString(),
                'statusPendaftaran' => 'menunggu',
            ]);
        });

        return redirect()->route('influencer.campaign', ['pilih' => $campaign->idCampaign])
            ->with('sukses', 'Pendaftaran berhasil dikirim. Admin akan memilih influencer untuk campaign ini.');
    }

    // Campaign yang boleh dilihat influencer:
    // yang sedang Berjalan dan belum lewat tanggal selesai, ditambah yang pernah ia daftarkan
    // (supaya influencer tetap bisa melihat campaign yang ia ikuti walaupun sudah berakhir).
    private function campaignTerlihat(Influencer $influencer): Builder
    {
        return Campaign::query()->where(function ($query) use ($influencer) {
            $query->where(fn ($q) => $q
                    ->where('statusCampaign', 'berjalan')
                    ->whereDate('tanggalSelesai', '>=', today()))
                ->orWhereIn('idCampaign', $influencer->pendaftaran()->select('idCampaign'));
        });
    }

    // Kriteria pendaftaran (Tabel 17, langkah 5). Mengembalikan alasan penolakan, atau null kalau boleh mendaftar.
    // Dipakai di dua tempat: untuk menampilkan keterangan di panel, dan untuk menolak di server.
    private function alasanTidakBisaDaftar(Influencer $influencer, Campaign $campaign): ?string
    {
        if ($influencer->statusBlacklist) {
            return 'Akun Anda berstatus blacklist, sehingga tidak dapat mendaftar campaign.'
                . ($influencer->alasanBlacklistTampil() ? ' Alasan: ' . $influencer->alasanBlacklistTampil() : '');
        }

        if ($influencer->pendaftaran()->where('idCampaign', $campaign->idCampaign)->exists()) {
            return 'Anda sudah mendaftar di campaign ini.';
        }

        // Pesan dibedakan supaya influencer tahu alasannya
        if ($campaign->statusCampaign === 'selesai' || $campaign->tanggalSelesai->lt(today())) {
            return 'Campaign ini sudah berakhir pada ' . $campaign->tanggalSelesai->format('d/m/Y') . ', sehingga pendaftaran sudah ditutup.';
        }

        if ($campaign->statusCampaign !== 'berjalan') {
            return 'Pendaftaran campaign ini belum dibuka.';
        }

        if ($campaign->kategoriInfluencer !== $influencer->kategori) {
            return "Campaign ini khusus untuk influencer kategori {$campaign->kategoriInfluencer}.";
        }

        // Influencer wajib punya akun untuk SEMUA platform di ketentuan campaign (Tabel 17: "memenuhi ketentuan campaign").
        // Tanpa pengecekan ini, influencer bisa diterima tetapi tidak bisa mengirim draft untuk platform yang tidak dimiliki,
        // dan pembayaran tidak bisa diproses karena setiap platform di ketentuan harus punya hasil pengerjaan.
        $platformDibutuhkan = $campaign->ketentuanPlatform->pluck('platform')->unique();

        // Platform milik influencer, dijadikan huruf kecil supaya "TikTok" dan "tiktok" dianggap sama
        $platformSaya = $influencer->akunSosmed()->pluck('platform')->map(fn ($platform) => Str::lower($platform));

        // Platform yang dibutuhkan campaign tetapi belum dimiliki influencer
        $belumPunya = $platformDibutuhkan
            ->reject(fn ($platform) => $platformSaya->contains(Str::lower($platform)))
            ->values();

        if ($belumPunya->isNotEmpty()) {
            // join(', ', ' dan ') menghasilkan contoh: "Instagram" atau "Instagram dan TikTok"
            $daftarPlatform = $belumPunya->join(', ', ' dan ');

            return "Anda belum memiliki akun {$daftarPlatform}. Tambahkan akun {$daftarPlatform} di menu Profil untuk mengikuti campaign ini.";
        }

        $jumlahDiterima = $campaign->pendaftaran()->where('statusPendaftaran', 'diterima')->count();

        if ($jumlahDiterima >= $campaign->kuotaInfluencer) {
            return 'Kuota influencer campaign ini sudah penuh.';
        }

        return null;
    }
}