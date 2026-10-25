// Dialog konfirmasi sebelum aksi penting.
// Cara pakai:
//   <form method="POST" data-konfirmasi="Pertanyaan?">          konfirmasi untuk semua tombol di form
//   <button type="submit" data-konfirmasi="Pertanyaan?">        konfirmasi hanya untuk tombol ini
// Ini hanya untuk kenyamanan. Pengecekan sebenarnya tetap dilakukan di controller.
document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', (event) => {
        // event.submitter = tombol yang diklik (termasuk tombol di luar form yang memakai atribut form="...")
        const tombol = event.submitter;
        const pertanyaan = tombol?.dataset.konfirmasi ?? form.dataset.konfirmasi;

        // Form tanpa konfirmasi (misalnya form pencarian) langsung dikirim
        if (!pertanyaan) {
            return;
        }

        if (!window.confirm(pertanyaan)) {
            event.preventDefault();   // pengguna menekan "Batal"
            return;
        }

        // Tombol dimatikan supaya form tidak terkirim dua kali, dan kursornya menjadi tanda loading
        if (tombol) {
            tombol.disabled = true;
            tombol.classList.add('is-mengirim');
        }
    });
});