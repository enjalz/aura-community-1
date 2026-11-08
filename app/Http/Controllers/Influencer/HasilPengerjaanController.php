<?php

namespace App\Http\Controllers\Influencer;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\HasilPengerjaan;
use App\Models\Influencer;
use App\Models\Notifikasi;
use App\Models\PendaftaranCampaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Use case Mengirim Hasil Pengerjaan (Tabel 18) dan Mengirim Insight (Tabel 22).
// Wireframe Gambar 84. Satu baris tabel = satu campaign, detail per platform ditampilkan dalam tab.
// Alur per platform: kirim draft -> draft disetujui -> kirim link (draft dihapus) -> tunggu 7 hari -> kirim insight.
class HasilPengerjaanController extends Controller
{
    public function index(Request $request): View
    {
        $influencer = $request->user()->influencer;
        $daftar = $this->daftarPengerjaan($influencer);

        $terpilih = $daftar->first(fn ($baris) => $baris->pendaftaran->idPendaftaran === $request->integer('pilih'));

        // Tab yang terbuka: dari ?platform=, atau platform pertama yang belum selesai, atau platform pertama
        $platformAktif = null;

        if ($terpilih) {
            $diminta = Str::lower((string) $request->query('platform'));

            $platformAktif = ($terpilih->daftarPlatform->firstWhere('kunci', $diminta)
                ?? $terpilih->daftarPlatform->first(fn ($item) => $item->hasil?->statusInsight !== 'disetujui')
                ?? $terpilih->daftarPlatform->first())?->kunci;
        }

        return view('influencer.hasil-pengerjaan', [
            'daftar' => $daftar,
            'terpilih' => $terpilih,
            'platformAktif' => $platformAktif,
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

        // Pengiriman ditutup setelah campaign berakhir
        if ($pesan = $this->pesanSudahBerakhir($pendaftaran->campaign, 'draft')) {
            return back()->with('gagal', $pesan);
        }

        $aturanBerkas = config('aura.berkas.draft');
        $data = $request->validate(
            [
                'platform' => ['required', Rule::in($pendaftaran->campaign->ketentuanPlatform->pluck('platform')->all())],
                'draftKonten' => [
                    'required', 'file', 'mimes:' . $aturanBerkas['ekstensi'],
                    'max:' . $aturanBerkas['maksimal'],   // batas umum = batas video

                    // Foto punya batas lebih kecil dari video
                    function (string $atribut, $berkas, \Closure $gagal) use ($aturanBerkas) {
                        if (str_starts_with((string) $berkas->getMimeType(), 'image/')
                            && $berkas->getSize() > $aturanBerkas['maksimalFoto'] * 1024) {
                            $gagal('Ukuran foto maksimal ' . ($aturanBerkas['maksimalFoto'] / 1024) . ' MB.');
                        }
                    },
                ],
            ],
            [
                'draftKonten.required' => 'Pilih berkas draft yang akan dikirim.',
                'draftKonten.mimes' => 'Format berkas harus ' . str_replace(',', ', ', $aturanBerkas['ekstensi']) . '.',
                'draftKonten.max' => 'Ukuran video maksimal ' . ($aturanBerkas['maksimal'] / 1024) . ' MB.',
                'draftKonten.uploaded' => 'Berkas gagal diunggah. Ukurannya mungkin melebihi batas server.',
                'platform.in' => 'Platform tidak sesuai dengan ketentuan campaign.',
            ]
        );

        // Konten sama: satu draft dipakai untuk semua platform di ketentuan campaign.
        // Konten berbeda: draft hanya untuk platform yang dipilih.
        $campaign = $pendaftaran->campaign;
        $daftarPlatform = $campaign->kontenSama
            ? $campaign->ketentuanPlatform->pluck('platform')->unique()->values()
            : collect([$data['platform']]);

        $target = [];

        foreach ($daftarPlatform as $platform) {
            $akun = $influencer->akunSosmed()->where('platform', $platform)->first();

            if (! $akun) {
                return back()->with('gagal', "Anda belum memiliki akun {$platform} di profil.");
            }

            $hasil = $this->hasilUntukPlatform($pendaftaran, $platform);

            // Draft hanya bisa dikirim kalau belum pernah, atau sedang diminta revisi
            if ($hasil && $hasil->statusDraft !== 'revisi') {
                return back()->with('gagal', 'Draft untuk akun ini sedang diperiksa atau sudah disetujui.');
            }

            $target[] = ['akun' => $akun, 'hasil' => $hasil];
        }

        // Berkas disimpan sekali. store() memberi nama acak, sehingga nama asli dari pengguna tidak dipakai di server.
        $lokasiBaru = $request->file('draftKonten')->store('draft', 'local');
        $daftarLokasiLama = collect($target)->map(fn ($t) => $t['hasil']?->draftKonten)->filter()->unique();

        $isian = [
            'draftKonten' => $lokasiBaru,
            'tanggalPengirimanDraft' => now(),
            'statusDraft' => 'menunggu',
        ];

        foreach ($target as $t) {
            if ($t['hasil']) {
                $t['hasil']->update($isian);
            } else {
                HasilPengerjaan::create([
                    ...$isian,
                    'idPendaftaran' => $pendaftaran->idPendaftaran,
                    'idAkun' => $t['akun']->idAkun,
                    'statusInsight' => 'belum_dikirim',
                ]);
            }
        }

        // Berkas draft lama (sebelum revisi) dihapus supaya penyimpanan tidak menumpuk
        foreach ($daftarLokasiLama as $lokasiLama) {
            Storage::disk('local')->delete($lokasiLama);
        }

        // Notifikasi ke Admin: ada draft yang perlu diperiksa (Tabel 19)
        Notifikasi::kirimKeAdmin(
            'Draft baru perlu diperiksa',
            "{$request->user()->nama} mengirim draft {$campaign->namaCampaign} ({$daftarPlatform->join(', ')}).",
            route('admin.hasil', [], false)
        );

        return redirect()->route('influencer.hasil', $this->tujuan($pendaftaran, $data['platform']))
            ->with('sukses', 'Draft berhasil dikirim dan menunggu pemeriksaan Admin.');
    }

    // Tabel 22 bagian 1: mengirim link konten yang sudah tayang.
    // Setelah link dikirim, berkas draft dihapus untuk menghemat penyimpanan.
    public function kirimLink(Request $request, HasilPengerjaan $hasil): RedirectResponse
    {
        $influencer = $request->user()->influencer;

        abort_unless($hasil->pendaftaran->idPengguna === $influencer->idPengguna, 404);

        if ($hasil->statusDraft !== 'disetujui') {
            return back()->with('gagal', 'Link konten dapat dikirim setelah draft disetujui Admin.');
        }

        if ($hasil->linkKonten) {
            return back()->with('gagal', 'Link konten untuk akun ini sudah dikirim.');
        }

        if ($pesan = $this->pesanSudahBerakhir($hasil->pendaftaran->campaign, 'link konten')) {
            return back()->with('gagal', $pesan);
        }

        $data = $request->validate(
            ['linkKonten' => ['required', 'url:http,https', 'max:255']],
            [
                'linkKonten.required' => 'Link Konten wajib diisi.',
                'linkKonten.url' => 'Link Konten harus berupa tautan lengkap, contoh https://instagram.com/p/...',
            ]
        );

        $lokasiDraft = $hasil->draftKonten;

        $hasil->update([
            'linkKonten' => $data['linkKonten'],
            'tanggalPengirimanLink' => now(),
        ]);

        // Hapus berkas draft kalau tidak ada lagi platform yang masih membutuhkannya.
        // Pada campaign konten sama, satu berkas dipakai beberapa platform, jadi baru dihapus
        // setelah semua platform yang memakai berkas itu mengirim link.
        if ($lokasiDraft && ! HasilPengerjaan::where('draftKonten', $lokasiDraft)->whereNull('linkKonten')->exists()) {
            Storage::disk('local')->delete($lokasiDraft);
            HasilPengerjaan::where('draftKonten', $lokasiDraft)->update(['draftKonten' => null]);
        }

        $tanggalInsight = $hasil->fresh()->tanggalBolehKirimInsight()->format('d/m/Y');

        return redirect()->route('influencer.hasil', $this->tujuan($hasil->pendaftaran, $hasil->akunSosmed->platform))
            ->with('sukses', "Link konten berhasil dikirim. Insight dapat dikirim mulai {$tanggalInsight}.");
    }

    // Tabel 22 bagian 2: mengirim tangkapan layar insight dan angka metrik,
    // paling cepat 7 hari setelah link konten dikirim
    public function kirimInsight(Request $request, HasilPengerjaan $hasil): RedirectResponse
    {
        $influencer = $request->user()->influencer;

        abort_unless($hasil->pendaftaran->idPengguna === $influencer->idPengguna, 404);

        if ($hasil->statusDraft !== 'disetujui') {
            return back()->with('gagal', 'Insight dapat dikirim setelah draft disetujui Admin.');
        }

        if (! $hasil->linkKonten) {
            return back()->with('gagal', 'Kirim link konten terlebih dahulu.');
        }

        // Masa tunggu insight belum lewat (dicek juga di server, bukan hanya di tampilan)
        if (! $hasil->bolehKirimInsight()) {
            return back()->with('gagal', 'Insight dapat dikirim mulai ' . $hasil->tanggalBolehKirimInsight()->format('d/m/Y') . '.');
        }

        if (! in_array($hasil->statusInsight, ['belum_dikirim', 'ditolak'], true)) {
            return back()->with('gagal', 'Insight sudah dikirim dan sedang diperiksa atau sudah disetujui.');
        }

        // Pengiriman ditutup setelah campaign berakhir
        if ($pesan = $this->pesanSudahBerakhir($hasil->pendaftaran->campaign, 'insight')) {
            return back()->with('gagal', $pesan);
        }

        $aturanBerkas = config('aura.berkas.insight');
        $data = $request->validate(
            [
                'fileInsight' => ['required', 'image', 'mimes:' . $aturanBerkas['ekstensi'], 'max:' . $aturanBerkas['maksimal']],
                'jumlahViews' => ['required', 'integer', 'min:0', 'max:2147483647'],
                'jumlahLikes' => ['required', 'integer', 'min:0', 'max:2147483647'],
                'jumlahKomentar' => ['required', 'integer', 'min:0', 'max:2147483647'],
                'jumlahShare' => ['required', 'integer', 'min:0', 'max:2147483647'],
                'jumlahSave' => ['required', 'integer', 'min:0', 'max:2147483647'],
                'jumlahPengikut' => ['required', 'integer', 'min:1', 'max:2147483647'],
            ],
            [
                'required' => ':attribute wajib diisi.',
                'image' => 'Tangkapan layar harus berupa gambar.',
                'mimes' => 'Format gambar harus jpg, png, atau webp.',
                'fileInsight.max' => 'Ukuran gambar maksimal 5 MB.',
                'integer' => ':attribute harus berupa angka bulat.',
                'min' => ':attribute tidak boleh negatif.',
                'jumlahPengikut.min' => 'Jumlah Pengikut minimal 1.',
            ],
            [
                'fileInsight' => 'Tangkapan Layar Insight',
                'jumlahViews' => 'Jumlah Views',
                'jumlahLikes' => 'Jumlah Likes',
                'jumlahKomentar' => 'Jumlah Komentar',
                'jumlahShare' => 'Jumlah Share',
                'jumlahSave' => 'Jumlah Save',
                'jumlahPengikut' => 'Jumlah Pengikut',
            ]
        );

        $lokasiLama = $hasil->fileInsight;

        $hasil->update([
            'fileInsight' => $request->file('fileInsight')->store('insight', 'local'),
            'jumlahViews' => $data['jumlahViews'],
            'jumlahLikes' => $data['jumlahLikes'],
            'jumlahKomentar' => $data['jumlahKomentar'],
            'jumlahShare' => $data['jumlahShare'],
            'jumlahSave' => $data['jumlahSave'],
            'jumlahPengikut' => $data['jumlahPengikut'],
            'tanggalPengirimanInsight' => now(),
            'statusInsight' => 'menunggu',
        ]);

        // Tangkapan layar lama (yang ditolak) dihapus
        if ($lokasiLama) {
            Storage::disk('local')->delete($lokasiLama);
        }

        // Notifikasi ke Admin: ada insight yang perlu diperiksa (Tabel 23)
        Notifikasi::kirimKeAdmin(
            'Insight baru perlu diperiksa',
            "{$request->user()->nama} mengirim insight {$hasil->pendaftaran->campaign->namaCampaign} ({$hasil->akunSosmed->platform}).",
            route('admin.insight', ['pilih' => $hasil->idHasil], false)
        );

        return redirect()->route('influencer.hasil', $this->tujuan($hasil->pendaftaran, $hasil->akunSosmed->platform))
            ->with('sukses', 'Insight berhasil dikirim dan menunggu pemeriksaan Admin.');
    }

    // Satu baris di tabel = satu campaign yang diterima.
    // Setiap baris berisi daftar platform dari ketentuan campaign, masing-masing dengan akun dan hasil pengerjaannya.
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

        return $daftarPendaftaran->map(fn (PendaftaranCampaign $pendaftaran) => (object) [
            'pendaftaran' => $pendaftaran,
            'campaign' => $pendaftaran->campaign,
            'daftarPlatform' => $pendaftaran->campaign->ketentuanPlatform
                ->pluck('platform')
                ->unique()
                ->values()
                ->map(fn (string $platform) => (object) [
                    'nama' => $platform,
                    'kunci' => Str::lower($platform),
                    'akun' => $akunSaya->get($platform),
                    'hasil' => $pendaftaran->hasilPengerjaan->first(fn ($h) => $h->akunSosmed->platform === $platform),
                ]),
        ])->values();
    }

    private function hasilUntukPlatform(PendaftaranCampaign $pendaftaran, string $platform): ?HasilPengerjaan
    {
        return $pendaftaran->hasilPengerjaan()
            ->whereHas('akunSosmed', fn ($q) => $q->where('platform', $platform))
            ->first();
    }

    // Batas akhir pengiriman draft, link, dan insight adalah Tanggal Selesai campaign.
    // Mengembalikan pesan penolakan, atau null kalau campaign belum berakhir.
    private function pesanSudahBerakhir(Campaign $campaign, string $jenis): ?string
    {
        if (! $campaign->tanggalSelesai->lt(today())) {
            return null;
        }

        return "Campaign sudah berakhir pada {$campaign->tanggalSelesai->format('d/m/Y')}, sehingga {$jenis} tidak dapat dikirim lagi.";
    }

    // Parameter halaman setelah mengirim: campaign yang sama dan tab platform yang barusan dikirim
    private function tujuan(PendaftaranCampaign $pendaftaran, string $platform): array
    {
        return ['pilih' => $pendaftaran->idPendaftaran, 'platform' => Str::lower($platform)];
    }
}