<?php

namespace App\Http\Controllers\Influencer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfilInfluencerRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

// Use case Mengelola Profil untuk Influencer (Tabel 7, wireframe Gambar 86)
class ProfilController extends Controller
{
    public function edit(Request $request): View
    {
        $pengguna = $request->user();

        return view('influencer.profil', [
            'pengguna' => $pengguna,
            'influencer' => $pengguna->influencer->load(['akunSosmed', 'rekening']),
        ]);
    }

    public function update(ProfilInfluencerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $pengguna = $request->user();
        $influencer = $pengguna->influencer;

        // Pengguna, Influencer, AkunSosmed, dan RekeningInfluencer disimpan bersama
        DB::transaction(function () use ($pengguna, $influencer, $data) {
            $pengguna->perbaruiProfil($data);
            $influencer->update(['kategori' => $data['kategori']]);
            $influencer->simpanAkunSosmed($data);

            if (filled($data['nomorRekening'] ?? null)) {
                // updateOrCreate di relasi hasOne: rekening yang ada diperbarui, kalau belum ada dibuat
                $influencer->rekening()->updateOrCreate([], [
                    'jenisRekening' => $data['jenisRekening'],
                    'namaPenyedia' => $data['namaPenyedia'],
                    'nomorRekening' => $data['nomorRekening'],
                    'namaPemilik' => $data['namaPemilik'],
                ]);
            }
        });

        return redirect()->route('influencer.profil')->with('sukses', 'Profil berhasil diperbarui.');
    }
}