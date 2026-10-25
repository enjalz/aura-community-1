<?php

namespace App\Http\Controllers\OwnerBrand;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfilRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

// Use case Mengelola Profil untuk Owner Brand (Tabel 7, wireframe Gambar 91)
class ProfilController extends Controller
{
    public function edit(Request $request): View
    {
        $pengguna = $request->user();

        return view('owner-brand.profil', [
            'pengguna' => $pengguna,
            'ownerBrand' => $pengguna->ownerBrand,
        ]);
    }

    public function update(ProfilRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $pengguna = $request->user();

        // Data di tabel Pengguna dan OwnerBrand disimpan bersama
        DB::transaction(function () use ($pengguna, $data) {
            $pengguna->perbaruiProfil($data);
            $pengguna->ownerBrand->update([
                'namaBrand' => $data['namaBrand'],
                'namaPerusahaan' => $data['namaPerusahaan'],
            ]);
        });

        return redirect()->route('owner.profil')->with('sukses', 'Profil berhasil diperbarui.');
    }
}