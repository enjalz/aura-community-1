// Teks panjang (deskripsi campaign) dipotong beberapa baris dengan tombol Lihat selengkapnya / Sembunyikan.
// Tombol hanya ditampilkan kalau teksnya memang lebih panjang dari batas baris.
document.querySelectorAll('[data-teks-ringkas]').forEach(function (wadah) {
    const isi = wadah.querySelector('.teks-ringkas-isi');
    const tombol = wadah.querySelector('[data-teks-ringkas-tombol]');

    // scrollHeight = tinggi teks utuh, clientHeight = tinggi yang terlihat setelah dipotong
    if (isi.scrollHeight > isi.clientHeight + 1) {
        tombol.hidden = false;
    }

    tombol.addEventListener('click', function () {
        const terbuka = wadah.classList.toggle('is-terbuka');
        tombol.textContent = terbuka ? 'Sembunyikan' : 'Lihat selengkapnya';
        tombol.setAttribute('aria-expanded', terbuka ? 'true' : 'false');
    });
});