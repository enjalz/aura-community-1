// Cek ukuran berkas sebelum di-upload (revisi dosen: video maks 500 MB, foto maks 10 MB).
// Dipakai pada <input type="file" data-maks-video="500" data-maks-foto="10">
document.querySelectorAll('input[type="file"][data-maks-video]').forEach(function (input) {
    input.addEventListener('change', function () {
        const berkas = input.files[0];
        if (!berkas) return;

        const ukuranMb = berkas.size / (1024 * 1024);
        const video = berkas.type.startsWith('video/');
        const batas = Number(video ? input.dataset.maksVideo : input.dataset.maksFoto);

        if (ukuranMb > batas) {
            alert(
                'Ukuran ' + (video ? 'video' : 'foto') + ' ' + ukuranMb.toFixed(1) + ' MB melebihi batas ' + batas + ' MB.\n' +
                'Silakan kompres atau pilih berkas lain.'
            );
            input.value = '';  // kosongkan pilihan, jadi tidak bisa dikirim
        }
    });
});