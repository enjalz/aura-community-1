<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OwnerBrandRequest;
use App\Models\OwnerBrand;
use App\Models\Pengguna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

// Use case Membuat Akun Owner Brand (Tabel 12) dan Mengelola Data Owner Brand (Tabel 13)
class DataOwnerBrandController extends Controller
{
    private const PER_HALAMAN = 10;

    // Daftar owner brand dengan pencarian nama brand atau perusahaan (wireframe Gambar 71)
    public function index(Request $request): View
    {
        $cari = trim((string) $request->query('cari'));

        $daftar = OwnerBrand::with('pengguna')
            ->when($cari !== '', function ($query) use ($cari) {
                $pola = '%' . addcslashes($cari, '%_\\') . '%';

                $query->where(function ($query) use ($pola) {
                    $query->where('namaBrand', 'like', $pola)
                        ->orWhere('namaPerusahaan', 'like', $pola);
                });
            })
            ->orderByDesc('idPengguna')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('admin.data-owner-brand.index', [
            'daftar' => $daftar,
            'cari' => $cari,
        ]);
    }

    // Tabel 12, langkah 1-2: menampilkan formulir pembuatan akun
    public function create(): View
    {
        return view('admin.data-owner-brand.form', ['ownerBrand' => null]);
    }

    // Tabel 12, langkah 3-7: menyimpan akun ke tabel Pengguna dan OwnerBrand
    public function store(OwnerBrandRequest $request): RedirectResponse
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

            OwnerBrand::create([
                'idPengguna' => $pengguna->idPengguna,
                'namaBrand' => $data['namaBrand'],
                'namaPerusahaan' => $data['namaPerusahaan'],
            ]);
        });

        // Tabel 12, langkah 8: pengingat untuk memberikan email dan password kepada brand.
        // Password sengaja tidak ditampilkan ulang, karena Admin sendiri yang mengetiknya.
        return redirect()->route('admin.owner-brand')->with(
            'sukses',
            "Akun {$data['namaBrand']} berhasil dibuat. Berikan email {$data['email']} dan password yang Anda tentukan kepada pihak brand untuk login."
        );
    }

    // Detail owner brand, termasuk campaign yang sudah diberi akses
    public function show(OwnerBrand $ownerBrand): View
    {
        $ownerBrand->load(['pengguna', 'campaign']);

        return view('admin.data-owner-brand.detail', ['ownerBrand' => $ownerBrand]);
    }

    public function edit(OwnerBrand $ownerBrand): View
    {
        $ownerBrand->load('pengguna');

        return view('admin.data-owner-brand.form', ['ownerBrand' => $ownerBrand]);
    }

    public function update(OwnerBrandRequest $request, OwnerBrand $ownerBrand): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $ownerBrand) {
            $pengguna = $ownerBrand->pengguna;
            $pengguna->fill([
                'nama' => $data['nama'],
                'email' => $data['email'],
                'noTelepon' => $data['noTelepon'],
                'alamat' => $data['alamat'],
                'statusAkun' => $data['statusAkun'],
            ]);

            // Password hanya diganti kalau kolomnya diisi
            if (! empty($data['password'])) {
                $pengguna->password = $data['password'];
            }

            $pengguna->save();

            $ownerBrand->update([
                'namaBrand' => $data['namaBrand'],
                'namaPerusahaan' => $data['namaPerusahaan'],
            ]);
        });

        return redirect()->route('admin.owner-brand.detail', $ownerBrand)
            ->with('sukses', 'Data owner brand berhasil diperbarui.');
    }

    // Menghapus baris Pengguna. Baris OwnerBrand dan AksesCampaign ikut terhapus (ON DELETE CASCADE).
    // Campaign-nya sendiri tidak terhapus, karena campaign dikelola Admin.
    public function destroy(OwnerBrand $ownerBrand): RedirectResponse
    {
        $namaBrand = $ownerBrand->namaBrand;
        $ownerBrand->pengguna->delete();

        return redirect()->route('admin.owner-brand')
            ->with('sukses', "Akun {$namaBrand} berhasil dihapus.");
    }
}