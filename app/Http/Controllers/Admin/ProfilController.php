<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfilRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Use case Mengelola Profil untuk Admin (Tabel 7, wireframe Gambar 78)
class ProfilController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.profil', ['pengguna' => $request->user()]);
    }

    public function update(ProfilRequest $request): RedirectResponse
    {
        $request->user()->perbaruiProfil($request->validated());

        return redirect()->route('admin.profil')->with('sukses', 'Profil berhasil diperbarui.');
    }
}