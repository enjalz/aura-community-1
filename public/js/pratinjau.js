// Video di jendela "Perbesar" dihentikan otomatis saat jendelanya ditutup,
// supaya suaranya tidak tetap terdengar di belakang.
// 'hidden.bs.modal' adalah event dari Bootstrap yang muncul setelah modal tertutup.
document.querySelectorAll('.modal-pratinjau').forEach((modal) => {
    modal.addEventListener('hidden.bs.modal', () => {
        modal.querySelectorAll('video').forEach((video) => video.pause());
    });

    // Saat jendela besar dibuka, video kecil di halaman dihentikan supaya tidak terputar dua kali bersamaan
    modal.addEventListener('show.bs.modal', () => {
        document.querySelectorAll('.pratinjau video').forEach((video) => video.pause());
    });
});