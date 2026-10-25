// Perilaku landing page saat digulir.
// Dimuat oleh resources/views/landing.blade.php, SETELAH JavaScript Bootstrap (layouts/app.blade.php),
// sehingga objek "bootstrap" sudah tersedia di sini.

const navbar = document.querySelector('.navbar-landing');
const menuLipat = document.getElementById('menu-landing');


// ---------------------------------------------------------------------
// 1. NAVBAR SAAT DIGULIR
// Class is-tergulir dipasang setelah halaman turun sedikit.
// CSS-nya (landing.css) membuat navbar lebih ramping dan agak transparan.
// ---------------------------------------------------------------------
const perbaruiNavbar = () => {
    navbar.classList.toggle('is-tergulir', window.scrollY > 8);
};

perbaruiNavbar();   // langsung dicek, karena halaman bisa dibuka dalam keadaan sudah tergulir (misalnya setelah refresh)

// passive: true = memberi tahu browser bahwa kode ini tidak menghentikan gulir, sehingga gulir tetap mulus
window.addEventListener('scroll', perbaruiNavbar, { passive: true });


// ---------------------------------------------------------------------
// 2. MENU AKTIF MENGIKUTI BAGIAN YANG SEDANG DILIHAT
// ScrollSpy (bawaan Bootstrap) memindahkan class "active" ke menu sesuai bagian (id) yang terlihat.
// rootMargin -55% = bagian dianggap "sedang dilihat" saat sudah melewati kira-kira tengah layar.
// ---------------------------------------------------------------------
new bootstrap.ScrollSpy(document.body, {
    target: '#menu-landing',
    rootMargin: '0px 0px -55%',
});

// Di HP: menu lipat ditutup otomatis setelah salah satu menu diklik
menuLipat.querySelectorAll('.nav-link').forEach((tautan) => {
    tautan.addEventListener('click', () => {
        if (menuLipat.classList.contains('show')) {
            bootstrap.Collapse.getOrCreateInstance(menuLipat).hide();
        }
    });
});


// ---------------------------------------------------------------------
// 3. ANIMASI MUNCUL SAAT DIGULIR
// Elemen yang dianimasikan: isi hero, judul setiap bagian, dan setiap kolom (kartu, gambar, galeri).
// Class "muncul" dipasang oleh JavaScript, bukan ditulis di HTML. Jadi kalau JavaScript gagal dimuat,
// semua isi tetap terlihat normal tanpa animasi.
// ---------------------------------------------------------------------
const kurangiAnimasi = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (!kurangiAnimasi && 'IntersectionObserver' in window) {
    const sasaran = document.querySelectorAll('.hero .row > *, .bagian-kepala, .bagian .row > *');

    sasaran.forEach((elemen) => {
        elemen.classList.add('muncul');

        // Kolom yang berjajar muncul bergantian: kolom ke-1 langsung, ke-2 setelah 90 ms, dan seterusnya.
        // Dibatasi maksimal 3 langkah supaya galeri 6 gambar tidak menunggu terlalu lama.
        const urutan = Array.prototype.indexOf.call(elemen.parentElement.children, elemen);
        elemen.style.transitionDelay = `${Math.min(urutan, 3) * 90}ms`;
    });

    // IntersectionObserver memberi tahu saat sebuah elemen masuk ke layar
    const pengamat = new IntersectionObserver((daftarEntri) => {
        daftarEntri.forEach((entri) => {
            if (entri.isIntersecting) {
                entri.target.classList.add('is-terlihat');
                pengamat.unobserve(entri.target);   // cukup sekali, tidak diulang saat digulir naik-turun
            }
        });
    }, {
        threshold: 0.15,                     // muncul saat 15% bagian elemen sudah terlihat
        rootMargin: '0px 0px -40px 0px',     // sedikit sebelum benar-benar sampai di bawah layar
    });

    sasaran.forEach((elemen) => pengamat.observe(elemen));
}