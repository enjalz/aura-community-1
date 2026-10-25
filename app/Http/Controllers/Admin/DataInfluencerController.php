<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InfluencerRequest;
use App\Models\AkunSosmed;
use App\Models\Influencer;
use App\Models\Pengguna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

// Use case Mengelola Data Influencer (Tabel 10) dan Blacklist Influencer (Tabel 11).
class DataInfluencerController extends Controller
{
    private const PER_HALAMAN = 10;

    // Daftar influencer terverifikasi, dengan pencarian dan filter (wireframe Gambar 70)
    public function index(Request $request): View
    {
        $cari = trim((string) $request->query('cari'));
        $kategori = $request->query('kategori');
        $status = $request->query('status');

        $daftar = Influencer::with(['pengguna', 'akunSosmed'])
            ->where('statusVerifikasi', 'diverifikasi')

            // Cari di nama pengguna ATAU username media sosial.
            // addcslashes: tanda % dan _ yang diketik dianggap huruf biasa, bukan simbol khusus LIKE.
            ->when($cari !== '', function ($query) use ($cari) {
                $pola = '%' . addcslashes($cari, '%_\\') . '%';

                $query->where(function ($query) use ($pola) {
                    $query->whereHas('pengguna', fn ($q) => $q->where('nama', 'like', $pola))
                        ->orWhereHas('akunSosmed', fn ($q) => $q->where('username', 'like', $pola));
                });
            })

            // Filter hanya dipakai kalau nilainya valid
            ->when(in_array($kategori, config('aura.kategori'), true), fn ($q) => $q->where('kategori', $kategori))
            ->when($status === 'aktif', fn ($q) => $q->where('statusBlacklist', false))
            ->when($status === 'blacklist', fn ($q) => $q->where('statusBlacklist', true))

            ->orderByDesc('idPengguna')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();   // pencarian dan filter tetap terbawa saat pindah halaman

        return view('admin.data-influencer.index', [
            'daftar' => $daftar,
            'kategori' => config('aura.kategori'),
            'filter' => compact('cari', 'kategori', 'status'),
        ]);
    }

    public function create(): View
    {
        return view('admin.data-influencer.form', [
            'influencer' => null,
            'kategori' => config('aura.kategori'),
        ]);
    }

    // Menambah influencer baru. Langsung terverifikasi karena dimasukkan sendiri oleh Admin.
    public function store(InfluencerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $pengguna = Pengguna::create([
                'nama' => $data['nama'],
                'email' => $data['email'],
                'password' => $data['password'],
                'noTelepon' => $data['noTelepon'],
                'alamat' => $data['alamat'],
                'statusAkun' => 'aktif',
            ]);

            Influencer::create([
                'idPengguna' => $pengguna->idPengguna,
                'kategori' => $data['kategori'],
                'statusVerifikasi' => 'diverifikasi',
            ]);

            $this->simpanAkunSosmed($pengguna->idPengguna, $data);
        });

        return redirect()->route('admin.influencer')
            ->with('sukses', "Influencer {$data['nama']} berhasil ditambahkan.");
    }

    // Detail influencer. Bagian blacklist juga ada di halaman ini (Tabel 11, langkah 3).
    public function show(Influencer $influencer): View
    {
        $influencer->load(['pengguna', 'akunSosmed', 'rekening']);

        return view('admin.data-influencer.detail', ['influencer' => $influencer]);
    }

    public function edit(Influencer $influencer): View
    {
        $influencer->load(['pengguna', 'akunSosmed']);

        return view('admin.data-influencer.form', [
            'influencer' => $influencer,
            'kategori' => config('aura.kategori'),
        ]);
    }

    public function update(InfluencerRequest $request, Influencer $influencer): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $influencer) {
            $pengguna = $influencer->pengguna;
            $pengguna->fill([
                'nama' => $data['nama'],
                'email' => $data['email'],
                'noTelepon' => $data['noTelepon'],
                'alamat' => $data['alamat'],
            ]);

            // Password hanya diganti kalau kolomnya diisi
            if (! empty($data['password'])) {
                $pengguna->password = $data['password'];
            }

            $pengguna->save();
            $influencer->update(['kategori' => $data['kategori']]);

            $this->simpanAkunSosmed($influencer->idPengguna, $data);
        });

        return redirect()->route('admin.influencer.detail', $influencer)
            ->with('sukses', 'Data influencer berhasil diperbarui.');
    }

    // Influencer yang sudah pernah mendaftar campaign tidak dihapus,
    // supaya riwayat hasil pengerjaan dan pembayaran tetap tersimpan.
    public function destroy(Influencer $influencer): RedirectResponse
    {
        if ($influencer->pendaftaran()->exists()) {
            return back()->with('gagal', 'Influencer ini sudah pernah mendaftar campaign, sehingga datanya tidak dapat dihapus. Gunakan blacklist bila diperlukan.');
        }

        $nama = $influencer->pengguna->nama;

        // Menghapus baris Pengguna. Baris Influencer dan AkunSosmed ikut terhapus (ON DELETE CASCADE di SQL).
        $influencer->pengguna->delete();

        return redirect()->route('admin.influencer')
            ->with('sukses', "Data influencer {$nama} berhasil dihapus.");
    }

    // Tabel 11, langkah 4-5: memberi status blacklist dan alasan (opsional)
    public function blacklist(Request $request, Influencer $influencer): RedirectResponse
    {
        $data = $request->validate(
            ['alasanBlacklist' => ['nullable', 'string', 'max:1000']],
            ['alasanBlacklist.max' => 'Alasan maksimal 1000 karakter.']
        );

        // Hanya berubah kalau belum di-blacklist, supaya tanggal blacklist lama tidak tertimpa
        $jumlahBerubah = Influencer::whereKey($influencer->idPengguna)
            ->where('statusBlacklist', false)
            ->update([
                'statusBlacklist' => true,
                'alasanBlacklist' => $data['alasanBlacklist'] ?? null,
                'tanggalBlacklist' => now()->toDateString(),
            ]);

        if ($jumlahBerubah === 0) {
            return back()->with('gagal', 'Influencer ini sudah berstatus blacklist.');
        }

        return redirect()->route('admin.influencer.detail', $influencer)
            ->with('sukses', "{$influencer->pengguna->nama} berhasil dimasukkan ke daftar blacklist.");
    }

        // Alternative scenario Tabel 11 (tambahan): mencabut status blacklist.
    // Alasan dan tanggal dikosongkan, karena tabel Influencer hanya menyimpan satu status blacklist.
    public function cabutBlacklist(Influencer $influencer): RedirectResponse
    {
        // Hanya berubah kalau memang sedang di-blacklist (mencegah klik dobel)
        $jumlahBerubah = Influencer::whereKey($influencer->idPengguna)
            ->where('statusBlacklist', true)
            ->update([
                'statusBlacklist' => false,
                'alasanBlacklist' => null,
                'tanggalBlacklist' => null,
            ]);

        if ($jumlahBerubah === 0) {
            return back()->with('gagal', 'Influencer ini tidak sedang berstatus blacklist.');
        }

        return redirect()->route('admin.influencer.detail', $influencer)
            ->with('sukses', "Status blacklist {$influencer->pengguna->nama} berhasil dicabut.");
    }

    // Menyimpan akun Instagram dan TikTok.
    // Diisi = dibuat baru atau diperbarui. Dikosongkan = dihapus.
    private function simpanAkunSosmed(int $idPengguna, array $data): void
    {
        foreach (config('aura.platform') as $kunci => $nama) {
            $kondisi = ['idPengguna' => $idPengguna, 'platform' => $nama];

            if (empty($data["{$kunci}_username"])) {
                AkunSosmed::where($kondisi)->delete();
                continue;
            }

            // updateOrCreate: cari akun dengan kondisi ini; kalau ada diperbarui, kalau tidak ada dibuat
            AkunSosmed::updateOrCreate($kondisi, [
                'username' => $data["{$kunci}_username"],
                'linkProfil' => $data["{$kunci}_link"],
                'jumlahPengikut' => $data["{$kunci}_pengikut"],
            ]);
        }
    }
}