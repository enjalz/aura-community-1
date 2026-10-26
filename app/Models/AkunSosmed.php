<?php

namespace App\Models;

use App\Services\RekapCampaign;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

// Tabel 31
class AkunSosmed extends ModelAura
{
    protected $table = 'AkunSosmed';
    protected $primaryKey = 'idAkun';
    protected $fillable = [
        'idPengguna', 'platform', 'username', 'linkProfil', 'jumlahPengikut',
        'domisiliAudiens', 'usiaDominan', 'genderDominan', 'fileDemografi',
    ];

    public function influencer(): BelongsTo
    {
        return $this->belongsTo(Influencer::class, 'idPengguna');
    }

    public function hasilPengerjaan(): HasMany
    {
        return $this->hasMany(HasilPengerjaan::class, 'idAkun');
    }

    // Insight yang sudah disetujui Admin dari campaign-campaign sebelumnya
    public function hasilDisetujui(): Collection
    {
        return $this->hasilPengerjaan->where('statusInsight', 'disetujui');
    }

    // Rata-rata view rate dari seluruh konten yang insight-nya disetujui. Null kalau belum ada riwayat.
    public function hitungViewRateHistoris(): ?float
    {
        $daftar = $this->hasilDisetujui()
            ->map(fn (HasilPengerjaan $hasil) => $hasil->viewRate())
            ->filter(fn ($nilai) => $nilai !== null);

        return $daftar->isNotEmpty() ? round($daftar->avg(), 2) : null;
    }

    // ER gabungan seluruh konten yang disetujui (rumus sama dengan RekapCampaign)
    public function hitungEngagementRateHistoris(): ?float
    {
        $hasil = $this->hasilDisetujui();

        return RekapCampaign::hitungEr(
            (int) $hasil->sum('jumlahLikes'),
            (int) $hasil->sum('jumlahKomentar'),
            (int) $hasil->sum('jumlahShare'),
            (int) $hasil->sum('jumlahSave'),
            (int) $hasil->sum('jumlahViews'),
        );
    }

    // Estimasi Jangkauan = Pengikut x View Rate Historis
    public function hitungEstimasiJangkauan(): ?int
    {
        $viewRate = $this->hitungViewRateHistoris();

        if ($viewRate === null || ! $this->jumlahPengikut) {
            return null;
        }

        return (int) round($this->jumlahPengikut * $viewRate / 100);
    }

    // Nama rentang pengikut akun ini, misalnya "5K–20K"
    public function rentangPengikut(): string
    {
        $daftar = config('aura.seleksi.rentangPengikut');

        foreach ($daftar as $nama => [$minimal, $maksimal]) {
            if ($this->jumlahPengikut >= $minimal && ($maksimal === null || $this->jumlahPengikut <= $maksimal)) {
                return $nama;
            }
        }

        return array_key_last($daftar);
    }

    // True kalau view rate jauh di bawah rata-rata akun lain di platform dan rentang pengikut yang sama
    public function cekIndikasiPengikut(?float $rataRataRentang): bool
    {
        $viewRate = $this->hitungViewRateHistoris();

        if ($viewRate === null || ! $rataRataRentang) {
            return false;
        }

        return $viewRate < $rataRataRentang * config('aura.seleksi.ambangViewRate') / 100;
    }

    // Aturan validasi demografi per platform (kunci field: tiktok_domisili, tiktok_usia, dst.)
    // $wajibIsian: domisili/usia/gender wajib kalau username diisi.
    // $wajibBerkas: screenshot wajib kalau username diisi.
    public static function aturanDemografi(string $kunci, bool $wajibIsian, bool $wajibBerkas): array
    {
        $berkas = config('aura.berkas.demografi');
        $wajib = fn (bool $ya) => $ya ? ["required_with:{$kunci}_username"] : [];

        return [
            "{$kunci}_domisili" => ['nullable', 'string', 'max:100', ...$wajib($wajibIsian)],
            "{$kunci}_usia" => ['nullable', Rule::in(array_keys(config('aura.rentangUsia'))), ...$wajib($wajibIsian)],
            "{$kunci}_gender" => ['nullable', Rule::in(array_keys(config('aura.genderAudiens'))), ...$wajib($wajibIsian)],
            "{$kunci}_demografi" => [
                'nullable', 'file', "mimes:{$berkas['ekstensi']}", "max:{$berkas['maksimal']}", ...$wajib($wajibBerkas),
            ],
        ];
    }

    public static function namaKolomDemografi(string $kunci, string $nama): array
    {
        return [
            "{$kunci}_domisili" => "Domisili audiens {$nama}",
            "{$kunci}_usia" => "Usia dominan audiens {$nama}",
            "{$kunci}_gender" => "Gender dominan audiens {$nama}",
            "{$kunci}_demografi" => "Screenshot insight audiens {$nama}",
        ];
    }
}