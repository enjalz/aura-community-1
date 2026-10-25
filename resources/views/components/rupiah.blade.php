{{-- Format angka menjadi Rupiah. Contoh: <x-rupiah :nilai="200000" /> menghasilkan "Rp 200.000" --}}
@props(['nilai'])
Rp {{ number_format((float) $nilai, 0, ',', '.') }}