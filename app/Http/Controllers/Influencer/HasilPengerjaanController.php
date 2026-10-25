<?php

namespace App\Http\Controllers\Influencer;

use App\Http\Controllers\Controller;
use App\Models\HasilPengerjaan;
use App\Models\Influencer;
use App\Models\PendaftaranCampaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Use case Mengirim Hasil Pengerjaan (Tabel 18) dan Mengirim Insight (Tabel 22).
// Wireframe Gambar 84.
class HasilPengerjaanController extends Controller
{
    public function index(Request $request): View
    {
        $influencer = $request->user()->influencer;
        $daftar = $this->daftarPengerjaan($influencer);

        // Baris yang dipilih, contoh ?pilih=3-instagram. Kalau tidak ada, baris pertama.
        $terpilih = $daftar->firstWhere('kunci', $request->query('pilih')) ?? $daftar->first();

        return view('influencer.hasil-pengerjaan', [
            'daftar' => $daftar,
            'terpilih' => $terpilih,
        ]);
    }

    // Tabel 18: mengunggah draft konten (pertama kali, atau kirim ulang setelah revisi)
    public function kirimDraft(Request $request, PendaftaranCampaign $pendaftaran): RedirectResponse
    {
        $influencer = $request->user()->influencer;

        // Pendaftaran milik influencer lain tidak boleh dipakai
        abort_unless($pendaftaran->idPengguna === $influencer->idPengguna, 404);

        // Entry condition Tabel 18: status pendaftaran diterima
        if ($pendaftaran->statusPendaftaran !== 'diterima') {
            return back()->with('gagal', 'Anda belum diterima di campaign ini.');
        }

        $aturanBerkas = config('aura.berkas.draft');
        $data = $request->validate(
            [
                'platform' => ['required', Rule::in($pendaftaran->campaign->ketentuanPlatform->pluck('platform')->all())],
                'draftKonten' => ['required', 'file', 'mimes:' . $aturanBerkas['ekstensi'], 'max:' . $aturanBerkas['maksimal']],
            ],
            [
                'draftKonten.required' => 'Pilih berkas draft yang akan dikirim.',
                'draftKonten.mimes' => 'Format berkas harus ' . str_replace(',', ', ', $aturanBerkas['ekstensi']) . '.',
                'draftKonten.max' => 'Ukuran berkas maksimal 100 MB.',
                'draftKonten.uploaded' => 'Berkas gagal diunggah. Ukurannya mungkin melebihi batas server.',
                'platform.in' => 'Platform tidak sesuai dengan ketentuan campaign.',
            ]
        );

        $akun = $influencer->akunSosmed()->where('platform', $data['platform'])->first();

        if (! $akun) {
            return back()->with('gagal', "Anda belum memiliki akun {$data['platform']} di profil.");
        }

        $hasil = $this->hasilUntukPlatform($pendaftaran, $data['platform']);

        // Draft hanya bisa dikirim kalau belum pernah, atau sedang diminta revisi
        if ($hasil && $hasil->statusDraft !== 'revisi') {
            return back()->with('gagal', 'Draft untuk akun ini sedang diperiksa atau sudah disetujui.');
        }

        // store() memberi nama acak pada berkas, sehingga nama asli dari pengguna tidak dipakai di server
        $lokasiBaru = $request->file('draftKonten')->store('draft', 'local');
        $lokasiLama = $hasil?->draftKonten;

        $isian = [
            'draftKonten' => $lokasiBaru,
            'tanggalPengirimanDraft' => now()->toDateString(),
            'statusDraft' => 'menunggu',
        ];

        if ($hasil) {
            $hasil->update($isian);
        } else {
            HasilPengerjaan::create([
                ...$isian,
                'idPendaftaran' => $pendaftaran->idPendaftaran,
                'idAkun' => $akun->idAkun,
                'statusInsight' => 'belum_dikirim',
            ]);
        }

        // Berkas draft lama (sebelum revisi) dihapus supaya penyimpanan tidak menumpuk
        if ($lokasiLama) {
            Storage::disk('local')->delete($lokasiLama);
        }

        return redirect()->route('influencer.hasil', ['pilih' => $this->kunci($pendaftaran, $data['platform'])])
            ->with('sukses', 'Draft berhasil dikirim dan menunggu pemeriksaan Admin.');
    }

    // Tabel 22: mengirim link konten yang sudah tayang, tangkapan layar insight, dan angka metrik
    public function kirimInsight(Request $request, HasilPengerjaan $hasil): RedirectResponse
    {
        $influencer = $request->user()->influencer;

        abort_unless($hasil->pendaftaran->idPengguna === $influencer->idPengguna, 404);

        // Entry condition Tabel 22: draft sudah disetujui Admin
        if ($hasil->statusDraft !== 'disetujui') {
            return back()->with('gagal', 'Insight dapat dikirim setelah draft disetujui Admin.');
        }

        if (! in_array($hasil->statusInsight, ['belum_dikirim', 'ditolak'], true)) {
            return back()->with('gagal', 'Insight sudah dikirim dan sedang diperiksa atau sudah disetujui.');
        }

        $aturanBerkas = config('aura.berkas.insight');
        $data = $request->validate(
            [
                'linkKonten' => ['required', 'url:http,https', 'max:255'],
                'fileInsight' => ['required', 'image', 'mimes:' . $aturanBerkas['ekstensi'], 'max:' . $aturanBerkas['maksimal']],
                'jumlahViews' => ['required', 'integer', 'min:0', 'max:2147483647'],
                'jumlahLikes' => ['required', 'integer', 'min:0', 'max:2147483647'],
                'jumlahKomentar' => ['required', 'integer', 'min:0', 'max:2147483647'],
            ],
            [
                'required' => ':attribute wajib diisi.',
                'url' => 'Link Konten harus berupa tautan lengkap, contoh https://instagram.com/p/...',
                'image' => 'Tangkapan layar harus berupa gambar.',
                'mimes' => 'Format gambar harus jpg, png, atau webp.',
                'fileInsight.max' => 'Ukuran gambar maksimal 5 MB.',
                'integer' => ':attribute harus berupa angka bulat.',
                'min' => ':attribute tidak boleh negatif.',
            ],
            [
                'linkKonten' => 'Link Konten',
                'fileInsight' => 'Tangkapan Layar Insight',
                'jumlahViews' => 'Jumlah Views',
                'jumlahLikes' => 'Jumlah Likes',
                'jumlahKomentar' => 'Jumlah Komentar',
            ]
        );

        $lokasiLama = $hasil->fileInsight;

        $hasil->update([
            'linkKonten' => $data['linkKonten'],
            'fileInsight' => $request->file('fileInsight')->store('insight', 'local'),
            'jumlahViews' => $data['jumlahViews'],
            'jumlahLikes' => $data['jumlahLikes'],
            'jumlahKomentar' => $data['jumlahKomentar'],
            'tanggalPengirimanInsight' => now()->toDateString(),
            'statusInsight' => 'menunggu',
        ]);

        // Tangkapan layar lama (yang ditolak) dihapus
        if ($lokasiLama) {
            Storage::disk('local')->delete($lokasiLama);
        }

        return redirect()->route('influencer.hasil', ['pilih' => $this->kunci($hasil->pendaftaran, $hasil->akunSosmed->platform)])
            ->with('sukses', 'Insight berhasil dikirim dan menunggu pemeriksaan Admin.');
    }

    // Satu baris di tabel = satu campaign yang diterima x satu platform di ketentuannya.
    // Contoh: Serum Wajah punya ketentuan Instagram dan TikTok, maka ada dua baris.
    private function daftarPengerjaan(Influencer $influencer): Collection
    {
        $akunSaya = $influencer->akunSosmed()->get()->keyBy('platform');

        $daftarPendaftaran = $influencer->pendaftaran()
            ->where('statusPendaftaran', 'diterima')
            ->with([
                'campaign.ketentuanPlatform',
                'hasilPengerjaan.akunSosmed',
                'hasilPengerjaan.feedback' => fn ($q) => $q->orderByDesc('idFeedback'),
            ])
            ->orderByDesc('idPendaftaran')
            ->get();

        return $daftarPendaftaran->flatMap(function (PendaftaranCampaign $pendaftaran) use ($akunSaya) {
            return $pendaftaran->campaign->ketentuanPlatform
                ->pluck('platform')
                ->unique()
                ->map(fn (string $platform) => (object) [
                    'kunci' => $this->kunci($pendaftaran, $platform),
                    'pendaftaran' => $pendaftaran,
                    'platform' => $platform,
                    'akun' => $akunSaya->get($platform),
                    'hasil' => $pendaftaran->hasilPengerjaan->first(fn ($h) => $h->akunSosmed->platform === $platform),
                ]);
        })->values();
    }

    private function hasilUntukPlatform(PendaftaranCampaign $pendaftaran, string $platform): ?HasilPengerjaan
    {
        return $pendaftaran->hasilPengerjaan()
            ->whereHas('akunSosmed', fn ($q) => $q->where('platform', $platform))
            ->first();
    }

    // Kunci baris untuk ?pilih=, contoh "3-instagram"
    private function kunci(PendaftaranCampaign $pendaftaran, string $platform): string
    {
        return $pendaftaran->idPendaftaran . '-' . Str::lower($platform);
    }
}