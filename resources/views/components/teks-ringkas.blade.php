{{-- Teks panjang yang dipotong beberapa baris, dengan tombol "Lihat selengkapnya".
     Pemakaian: <x-teks-ringkas :teks="$campaign->deskripsi" />
     baris = jumlah baris yang tampil sebelum dibuka (bawaan 4).
     Tombol baru muncul kalau teksnya memang lebih panjang dari batas (diatur teks-ringkas.js). --}}
@props(['teks', 'baris' => 4])

<div class="teks-ringkas" data-teks-ringkas style="--baris-ringkas: {{ (int) $baris }}">
    <div class="teks-ringkas-isi teks-panjang">{{ $teks }}</div>
    <button type="button" class="teks-ringkas-tombol" data-teks-ringkas-tombol aria-expanded="false" hidden>
        Lihat selengkapnya
    </button>
</div>