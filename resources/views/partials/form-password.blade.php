{{-- Bagian ganti password, dipakai di ketiga halaman profil.
     Kosongkan keduanya kalau password tidak diganti. --}}
<div class="row g-3">
    <div class="col-md-6">
        <x-input-password name="passwordSaatIni" label="Password Saat Ini" placeholder="Wajib diisi jika mengganti password" autocomplete="current-password" />
    </div>
    <div class="col-md-6">
        <x-input-password name="password" label="Password Baru" placeholder="Kosongkan jika tidak diubah" autocomplete="new-password" />
    </div>
</div>