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

            // Periode unggah harus di dalam periode campaign. Tanggal sama = unggah serempak satu hari.
            'tanggalUnggahMulai' => ['required', 'date', 'after_or_equal:tanggalMulai', 'before_or_equal:tanggalSelesai'],
            'tanggalUnggahSelesai' => ['required', 'date', 'after_or_equal:tanggalUnggahMulai', 'before_or_equal:tanggalSelesai'],

            'statusCampaign' => ['required', Rule::in(array_keys(config('aura.statusCampaign')))],
            'kuotaInfluencer' => ['required', 'integer', 'min:1', 'max:100000'],

            // Minimal satu ketentuan platform (kardinalitas 1..* di Tabel 1)
            'ketentuan' => ['required', 'array', 'min:1'],
            'ketentuan.*.platform' => ['required', Rule::in(config('aura.platform'))],
            'ketentuan.*.jenisKonten' => ['required', Rule::in(array_keys(config('aura.jenisKonten')))],
            'ketentuan.*.jumlahPostingan' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                // Kuota tidak boleh lebih kecil dari influencer yang sudah diterima
                $campaign = $this->route('campaign');

                if (! $campaign instanceof Campaign) {
                    return;
                }

                $jumlahDiterima = $campaign->pendaftaran()->where('statusPendaftaran', 'diterima')->count();

                if ((int) $this->input('kuotaInfluencer') < $jumlahDiterima) {
                    $validator->errors()->add('kuotaInfluencer', "Kuota minimal {$jumlahDiterima}, karena sudah ada {$jumlahDiterima} influencer yang diterima.");
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
            'statusCampaign' => 'Status',
            'kuotaInfluencer' => 'Kuota Influencer',
            'ketentuan.*.platform' => 'Platform',
            'ketentuan.*.jenisKonten' => 'Jenis Konten',
            'ketentuan.*.jumlahPostingan' => 'Jumlah Postingan',
        ];
    }
}