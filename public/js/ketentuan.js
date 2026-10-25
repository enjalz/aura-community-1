// Menambah dan menghapus baris Ketentuan Platform di form campaign.
// Dipakai oleh admin/campaign/form.blade.php.
const wadah = document.querySelector('[data-ketentuan]');

if (wadah) {
    const daftar = wadah.querySelector('[data-ketentuan-daftar]');
    const cetakan = document.getElementById('template-ketentuan');

    // Nomor untuk baris berikutnya. Selalu bertambah dan tidak pernah dipakai ulang,
    // supaya nama field (ketentuan[3][platform], dan seterusnya) tidak bentrok setelah ada baris yang dihapus.
    let indeksBerikut = Number(wadah.dataset.indeksBerikut);

    // Tombol hapus dimatikan kalau tersisa satu baris, karena campaign wajib punya minimal satu ketentuan
    const perbaruiTombolHapus = () => {
        const tinggalSatu = daftar.children.length <= 1;
        daftar.querySelectorAll('[data-hapus-ketentuan]').forEach((tombol) => {
            tombol.disabled = tinggalSatu;
        });
    };

    wadah.querySelector('[data-tambah-ketentuan]').addEventListener('click', () => {
        // Salin isi <template>, lalu ganti {indeks} dengan nomor baris baru
        const html = cetakan.innerHTML.replaceAll('{indeks}', indeksBerikut);
        indeksBerikut += 1;

        daftar.insertAdjacentHTML('beforeend', html);
        perbaruiTombolHapus();
    });

    // Satu pendengar klik untuk semua tombol hapus, termasuk baris yang baru ditambahkan
    daftar.addEventListener('click', (event) => {
        const tombol = event.target.closest('[data-hapus-ketentuan]');

        if (tombol && daftar.children.length > 1) {
            tombol.closest('[data-baris-ketentuan]').remove();
            perbaruiTombolHapus();
        }
    });

    perbaruiTombolHapus();
}