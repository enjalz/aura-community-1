<?php

namespace App\Http\Requests\Admin;

use App\Models\Campaign;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

// Validasi form Tambah dan Ubah Campaign (Tabel 14, alternative scenario: data tidak lengkap atau tidak sesuai)
class CampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'namaCampaign' => ['required', 'string', 'max:150'],
            'deskripsi' => ['required', 'string', 'max:5000'],
            'targetAudiens' => ['required', 'string', 'max:100'],
            'kategoriInfluencer' => ['required', Rule::in(config('aura.kategori'))],
            'lokasiCampaign' => ['required', 'string', 'max:100'],
            'targetUsia' => ['nullable', Rule::in(array_keys(config('aura.rentangUsia')))],
            'minimalPengikut' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'feePerInfluencer' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'tanggalMulai' => ['required', 'date'],
            'tanggalSelesai' => ['required', 'date', 'after_or_equal:tanggalMulai'],
            'wajibHadir' => ['required', 'boolean'],
            'kontenSama' => ['required', 'boolean'],

            // Periode unggah harus di dalam periode campaign. Tanggal sama = unggah serempak satu hari.
            'tanggalUnggahMulai' => ['required', 'date', 'after_or_equal:tanggalMulai', 'before_or_equal:tanggalSelesai'],
            'tanggalUnggahSelesai' => ['required', 'date', 'after_or_equal:tanggalUnggahMulai', 'before_or_equal:tanggalSelesai'],

            'statusCampaign' => ['required', Rule::in(array_keys(config('aura.statusCampaign')))],
            'kuotaInfluencer' => ['required', 'integer', 'min:1', 'max:100000'],

            // Minimal satu ketentuan platform (kardinalitas 1..* di Tabel 1)
            'ketentuan' => ['required', 'array', 'min:1'],
            'ketentuan.*.platform' => ['required', Rule::in(config('aura.platform'))],
            'ketentuan.*.jenisKonten' => ['required', Rule::in(array_keys(config('aura.jenisKonten')))],
            // Satu konten per platform: setiap platform punya satu draft, link, dan insight
            'ketentuan.*.jumlahPostingan' => ['required', 'integer', 'in:1'],
        ];
    }

    public function after(): array
    {
        return [
            // Kuota tidak boleh lebih kecil dari influencer yang sudah diterima
            function (Validator $validator) {
                $campaign = $this->route('campaign');

                if (! $campaign instanceof Campaign) {
                    return;
                }

                $jumlahDiterima = $campaign->pendaftaran()->where('statusPendaftaran', 'diterima')->count();

                if ((int) $this->input('kuotaInfluencer') < $jumlahDiterima) {
                    $validator->errors()->add('kuotaInfluencer', "Kuota minimal {$jumlahDiterima}, karena sudah ada {$jumlahDiterima} influencer yang diterima.");
                }
            },

            // Konten yang sama untuk semua platform hanya bisa kalau ketentuan setiap platform seimbang
            function (Validator $validator) {
                if (! $this->boolean('kontenSama')) {
                    return;
                }

                $ketentuan = collect($this->input('ketentuan', []));

                if ($ketentuan->pluck('platform')->unique()->count() < 2) {
                    $validator->errors()->add('kontenSama',
                        'Konten yang sama hanya dapat digunakan jika campaign memiliki lebih dari satu platform.');
                } elseif ($ketentuan->pluck('jumlahPostingan')->map(fn ($jumlah) => (int) $jumlah)->unique()->count() > 1) {
                    $validator->errors()->add('kontenSama',
                        'Konten yang sama tidak dapat digunakan karena jumlah postingan setiap platform berbeda. Samakan jumlah postingan atau hapus centang.');
                } elseif ($ketentuan->pluck('jenisKonten')->unique()->count() > 1) {
                    $validator->errors()->add('kontenSama',
                        'Konten yang sama tidak dapat digunakan karena jenis konten setiap platform berbeda. Samakan jenis konten atau hapus centang.');
                }

                // Saat Ubah: pilihan ini tidak boleh diganti kalau sudah ada draft yang dikirim
                $campaign = $this->route('campaign');

                if ($campaign instanceof Campaign
                    && $campaign->kontenSama !== $this->boolean('kontenSama')
                    && \App\Models\HasilPengerjaan::whereHas('pendaftaran', fn ($q) => $q->where('idCampaign', $campaign->idCampaign))->exists()) {
                    $validator->errors()->add('kontenSama',
                        'Pilihan konten tidak dapat diubah karena influencer sudah mengirim draft.');
                }
            },

            // Tanggal Selesai harus memberi waktu untuk masa tunggu insight setelah periode unggah berakhir
            function (Validator $validator) {
                $unggahSelesai = $this->date('tanggalUnggahSelesai');
                $selesai = $this->date('tanggalSelesai');
                $hari = config('aura.hariTungguInsight');

                if ($unggahSelesai && $selesai && $selesai->lt($unggahSelesai->copy()->addDays($hari))) {
                    $validator->errors()->add('tanggalSelesai',
                        "Tanggal Selesai minimal {$hari} hari setelah Tanggal Unggah Selesai, karena insight baru dapat dikirim {$hari} hari setelah konten diunggah.");
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'max.string' => ':attribute maksimal :max karakter.',
            'in' => 'Pilihan :attribute tidak valid.',
            'numeric' => ':attribute harus berupa angka.',
            'integer' => ':attribute harus berupa angka bulat.',
            'min.numeric' => ':attribute minimal :min.',
            'date' => ':attribute bukan tanggal yang valid.',
            'tanggalSelesai.after_or_equal' => 'Tanggal Selesai tidak boleh sebelum Tanggal Mulai.',
            'tanggalUnggahMulai.after_or_equal' => 'Tanggal Unggah Mulai tidak boleh sebelum Tanggal Mulai campaign.',
            'tanggalUnggahMulai.before_or_equal' => 'Tanggal Unggah Mulai tidak boleh setelah Tanggal Selesai campaign.',
            'tanggalUnggahSelesai.after_or_equal' => 'Tanggal Unggah Selesai tidak boleh sebelum Tanggal Unggah Mulai.',
            'tanggalUnggahSelesai.before_or_equal' => 'Tanggal Unggah Selesai tidak boleh setelah Tanggal Selesai campaign.',
            'ketentuan.required' => 'Tambahkan minimal satu ketentuan platform.',
            'ketentuan.*.jumlahPostingan.in' => 'Jumlah postingan per platform adalah 1. Tambahkan platform lain jika butuh lebih dari satu konten.',
        ];
    }

    public function attributes(): array
    {
        return [
            'namaCampaign' => 'Nama Campaign',
            'deskripsi' => 'Deskripsi',
            'targetAudiens' => 'Target Audiens',
            'kategoriInfluencer' => 'Kategori Influencer',
            'lokasiCampaign' => 'Lokasi Campaign',
            'targetUsia' => 'Target Usia',
            'minimalPengikut' => 'Minimal Pengikut',
            'feePerInfluencer' => 'Fee per Influencer',
            'tanggalMulai' => 'Tanggal Mulai',
            'tanggalSelesai' => 'Tanggal Selesai',
            'tanggalUnggahMulai' => 'Tanggal Unggah Mulai',
            'tanggalUnggahSelesai' => 'Tanggal Unggah Selesai',
            'wajibHadir' => 'Wajib Hadir',
            'kontenSama' => 'Konten yang Sama',
            'statusCampaign' => 'Status',
            'kuotaInfluencer' => 'Kuota Influencer',
            'ketentuan.*.platform' => 'Platform',
            'ketentuan.*.jenisKonten' => 'Jenis Konten',
            'ketentuan.*.jumlahPostingan' => 'Jumlah Postingan',
        ];
    }
}