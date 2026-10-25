// Ikon mata pada kolom password: klik untuk menampilkan atau menyembunyikan isi.
// Script dimuat di akhir <body>, jadi semua elemen sudah tersedia.
document.querySelectorAll('.tombol-mata').forEach((tombol) => {
    const input = document.getElementById(tombol.dataset.target);
    const ikon = tombol.querySelector('i');

    tombol.addEventListener('click', () => {
        const sedangTersembunyi = input.type === 'password';

        input.type = sedangTersembunyi ? 'text' : 'password';
        ikon.classList.toggle('bi-eye', !sedangTersembunyi);
        ikon.classList.toggle('bi-eye-slash', sedangTersembunyi);
        tombol.setAttribute('aria-label', sedangTersembunyi ? 'Sembunyikan password' : 'Tampilkan password');
    });
});