<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Notifikasi;
use App\Models\OwnerBrand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Use case Memberikan Akses Campaign (Tabel 15)
class AksesCampaignController extends Controller
{
    // Langkah 1-2: menampilkan informasi campaign dan daftar Owner Brand
    public function edit(Campaign $campaign): View
    {
        return view('admin.campaign.akses', [
            'campaign' => $campaign,
            'daftarOwnerBrand' => OwnerBrand::with('pengguna')->orderBy('namaBrand')->get(),
            'terpilih' => $campaign->ownerBrand()->pluck('OwnerBrand.idPengguna')->all(),
        ]);
    }

    // Langkah 3-5: menyimpan hubungan campaign dan Owner Brand di tabel AksesCampaign
    public function update(Request $request, Campaign $campaign): RedirectResponse
    {
        $data = $request->validate([
            'ownerBrand' => ['nullable', 'array'],
            'ownerBrand.*' => ['integer', Rule::exists('OwnerBrand', 'idPengguna')],
        ]);

        // Dicatat sebelum disimpan, supaya hanya Owner Brand yang BARU diberi akses yang dinotifikasi
        $aksesLama = $campaign->ownerBrand()->pluck('OwnerBrand.idPengguna')->all();

        // Sama seperti sync(), tetapi idAdmin ikut tersimpan di tabel AksesCampaign
        $campaign->ownerBrand()->syncWithPivotValues(
            $data['ownerBrand'] ?? [],
            ['idAdmin' => $request->user()->idPengguna]
        );

        // Notifikasi ke Owner Brand yang baru diberi akses
        $penerimaBaru = array_values(array_diff(array_map('intval', $data['ownerBrand'] ?? []), $aksesLama));

        Notifikasi::kirim(
            $penerimaBaru,
            'Akses campaign baru',
            "Anda diberi akses untuk memantau {$campaign->namaCampaign}.",
            route('owner.campaign', [], false)
        );

        // Tabel 15 alternative scenario: tidak memilih Owner Brand = tidak ada akses
        $pesan = empty($data['ownerBrand'])
            ? 'Tidak ada Owner Brand yang diberi akses ke campaign ini.'
            : 'Akses campaign berhasil disimpan.';

        return redirect()->route('admin.campaign.detail', $campaign)->with('sukses', $pesan);
    }
}