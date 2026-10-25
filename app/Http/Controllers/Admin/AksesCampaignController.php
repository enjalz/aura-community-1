<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
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

        // sync(): yang dicentang ditambahkan, yang tidak dicentang dihapus, yang sudah ada dibiarkan.
        $campaign->ownerBrand()->sync($data['ownerBrand'] ?? []);

        // Tabel 15 alternative scenario: tidak memilih Owner Brand = tidak ada akses
        $pesan = empty($data['ownerBrand'])
            ? 'Tidak ada Owner Brand yang diberi akses ke campaign ini.'
            : 'Akses campaign berhasil disimpan.';

        return redirect()->route('admin.campaign.detail', $campaign)->with('sukses', $pesan);
    }
}