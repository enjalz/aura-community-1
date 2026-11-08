<?php

namespace App\Http\Controllers\Influencer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfilInfluencerRequest;
use App\Models\Notifikasi;
use App\Models\Pembayaran;
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

        // Dicatat sebelum disimpan, supaya setelahnya bisa diketahui apa yang berubah (untuk notifikasi Admin)
        $idAkunMenungguLama = $influencer->akunSosmed()->where('statusVerifikasi', 'menunggu')->pluck('idAkun');
        $pembayaranTertahan = Pembayaran::with('pendaftaran.campaign')
            ->where('statusPembayaran', 'gagal')
            ->whereNotNull('rekeningGagal')
            ->whereHas('pendaftaran', fn ($q) => $q->where('idPengguna', $influencer->idPengguna))
            ->get()
            ->filter(fn ($pembayaran) => $pembayaran->menungguRekeningBaru($influencer->rekening));

        // Pengguna, Influencer, AkunSosmed, dan RekeningInfluencer disimpan bersama.
        // Akun media sosial yang ditambah atau diubah otomatis berstatus menunggu verifikasi (lihat AkunSosmed::booted).
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

        // Akun media sosial yang baru masuk antrean verifikasi
        $akunBaruMenunggu = $influencer->akunSosmed()
            ->where('statusVerifikasi', 'menunggu')
            ->whereNotIn('idAkun', $idAkunMenungguLama)
            ->get();

        if ($akunBaruMenunggu->isNotEmpty()) {
            Notifikasi::kirimKeAdmin(
                'Akun media sosial perlu diverifikasi',
                "{$pengguna->nama} menambah atau mengubah akun "
                    . $akunBaruMenunggu->map(fn ($akun) => "{$akun->platform} @{$akun->username}")->join(', ', ' dan ') . '.',
                route('admin.verifikasi', [], false)
            );
        }

        // Rekening yang sebelumnya gagal menerima transfer sudah diganti: pembayaran siap diproses ulang
        $rekeningSekarang = $influencer->rekening()->first();

        foreach ($pembayaranTertahan as $pembayaran) {
            if (! $pembayaran->menungguRekeningBaru($rekeningSekarang)) {
                Notifikasi::kirimKeAdmin(
                    'Rekening influencer diperbarui',
                    "{$pengguna->nama} sudah memperbarui rekening. Pembayaran fee {$pembayaran->pendaftaran->campaign->namaCampaign} siap diproses ulang.",
                    route('admin.pembayaran', ['pilih' => $pembayaran->idPendaftaran], false)
                );
            }
        }

        $pesan = $influencer->akunSosmed()->where('statusVerifikasi', 'menunggu')->exists()
            ? 'Profil berhasil diperbarui. Akun media sosial yang ditambah atau diubah menunggu verifikasi Admin.'
            : 'Profil berhasil diperbarui.';

        return redirect()->route('influencer.profil')->with('sukses', $pesan);
    }
}