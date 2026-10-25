// Pilihan filter (kategori, status) langsung diterapkan saat diganti, tanpa tombol "Cari".
// Kolom pencarian tetap dikirim dengan menekan Enter.
// Dipakai di form yang punya atribut data-filter-otomatis.
document.querySelectorAll('form[data-filter-otomatis] select').forEach((pilihan) => {
    pilihan.addEventListener('change', () => {
        pilihan.form.submit();
    });
});