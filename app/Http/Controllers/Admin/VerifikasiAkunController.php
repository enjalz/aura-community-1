<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AkunSosmed;
use App\Models\Influencer;
use App\Models\Notifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Use case Verifikasi Akun (Tabel 8, Activity Diagram Gambar 16, Sequence Diagram Gambar 39).
// Bagian atas: akun influencer baru. Bagian bawah: akun media sosial yang ditambah atau diubah.
class VerifikasiAkunController extends Controller
{
    private const PER_HALAMAN = 5;

    // Langkah 1-4 Tabel 8: daftar akun menunggu dan data influencer yang dipilih
    public function index(Request $request): View
    {
        // Pendaftar paling lama tampil paling atas, supaya tidak ada yang menunggu terlalu lama.
        // with('pengguna') mengambil nama dan email sekaligus (tidak query ulang per baris).
        $daftar = Influencer::with('pengguna')
            ->where('statusVerifikasi', 'menunggu')
            ->orderBy('idPengguna')
            ->paginate(self::PER_HALAMAN);

        // Influencer yang tampil di panel kanan: dari tautan "Periksa" (?pilih=ID),
        // atau baris pertama di halaman ini kalau belum memilih.
        $terpilih = null;

        if ($request->integer('pilih')) {
            $terpilih = Influencer::where('statusVerifikasi', 'menunggu')->find($request->integer('pilih'));
        }

        $terpilih ??= $daftar->first();
        $terpilih?->load(['pengguna', 'akunSosmed']);

        // Akun media sosial yang ditambah atau diubah oleh influencer yang sudah terverifikasi
        $akunMenunggu = AkunSosmed::with('influencer.pengguna')
            ->where('statusVerifikasi', 'menunggu')
            ->whereHas('influencer', fn ($q) => $q->where('statusVerifikasi', 'diverifikasi'))
            ->orderBy('idAkun')
            ->get();

        return view('admin.verifikasi-akun', [
            'daftar' => $daftar,
            'terpilih' => $terpilih,
            'akunMenunggu' => $akunMenunggu,
        ]);
    }

    // Langkah 6-7 Tabel 8: Admin menyetujui
    public function setujui(Influencer $influencer): RedirectResponse
    {
        return $this->ubahStatus($influencer, 'diverifikasi', 'berhasil diverifikasi');
    }

    // Alternative scenario Tabel 8: data tidak memenuhi persyaratan
    public function tolak(Influencer $influencer): RedirectResponse
    {
        return $this->ubahStatus($influencer, 'ditolak', 'ditolak');
    }

    // Akun media sosial baru atau diubah: disetujui, sehingga bisa dipakai mendaftar campaign
    public function setujuiAkunSosmed(AkunSosmed $akun): RedirectResponse
    {
        // Syarat 'menunggu' mencegah proses dobel (klik ganda atau dua tab)
        $jumlahBerubah = AkunSosmed::whereKey($akun->idAkun)
            ->where('statusVerifikasi', 'menunggu')
            ->update(['statusVerifikasi' => 'diverifikasi', 'alasanPenolakan' => null]);

        if ($jumlahBerubah === 0) {
            return redirect()->route('admin.verifikasi')->with('gagal', 'Akun ini sudah diproses sebelumnya.');
        }

        Notifikasi::kirim(
            $akun->idPengguna,
            "Akun {$akun->platform} terverifikasi",
            "Akun {$akun->platform} @{$akun->username} sudah diverifikasi dan dapat dipakai untuk mendaftar campaign.",
            route('influencer.profil', [], false)
        );

        return redirect()->route('admin.verifikasi')
            ->with('sukses', "Akun {$akun->platform} @{$akun->username} berhasil diverifikasi.");
    }

    // Akun media sosial baru atau diubah: ditolak dengan alasan, influencer memperbaikinya di Profil
    public function tolakAkunSosmed(Request $request, AkunSosmed $akun): RedirectResponse
    {
        $data = $request->validate(
            ['alasan' => ['required', 'string', 'max:500']],
            [
                'alasan.required' => 'Tulis alasan penolakan supaya influencer tahu apa yang perlu diperbaiki.',
                'alasan.max' => 'Alasan maksimal 500 karakter.',
            ]
        );

        $jumlahBerubah = AkunSosmed::whereKey($akun->idAkun)
            ->where('statusVerifikasi', 'menunggu')
            ->update(['statusVerifikasi' => 'ditolak', 'alasanPenolakan' => $data['alasan']]);

        if ($jumlahBerubah === 0) {
            return redirect()->route('admin.verifikasi')->with('gagal', 'Akun ini sudah diproses sebelumnya.');
        }

        Notifikasi::kirim(
            $akun->idPengguna,
            "Akun {$akun->platform} ditolak",
            "Akun {$akun->platform} @{$akun->username} ditolak: {$data['alasan']} Perbaiki datanya di Profil.",
            route('influencer.profil', [], false)
        );

        return redirect()->route('admin.verifikasi')
            ->with('sukses', "Akun {$akun->platform} @{$akun->username} ditolak.");
    }

    // Syarat statusVerifikasi = 'menunggu' membuat satu akun hanya bisa diproses sekali.
    // Kalau tombol terklik dua kali, atau halaman dibuka di dua tab, klik kedua tidak mengubah apa pun.
    private function ubahStatus(Influencer $influencer, string $statusBaru, string $keterangan): RedirectResponse
    {
        $jumlahBerubah = Influencer::whereKey($influencer->idPengguna)
            ->where('statusVerifikasi', 'menunggu')
            ->update(['statusVerifikasi' => $statusBaru]);

        if ($jumlahBerubah === 0) {
            return redirect()->route('admin.verifikasi')
                ->with('gagal', 'Akun ini sudah diproses sebelumnya.');
        }

        // Notifikasi ke influencer: hasil verifikasi akunnya
        if ($statusBaru === 'diverifikasi') {
            Notifikasi::kirim(
                $influencer->idPengguna,
                'Akun Anda sudah diverifikasi',
                'Selamat bergabung di Aura Community. Anda sekarang dapat melihat dan mendaftar campaign.',
                route('influencer.campaign', [], false)
            );
        } else {
            Notifikasi::kirim(
                $influencer->idPengguna,
                'Verifikasi akun ditolak',
                'Data registrasi Anda belum memenuhi persyaratan Aura Community.',
                route('influencer.status', [], false)
            );
        }

        return redirect()->route('admin.verifikasi')
            ->with('sukses', "Akun {$influencer->pengguna->nama} {$keterangan}.");
    }
}