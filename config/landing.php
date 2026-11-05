<?php

// Isi teks landing page (wireframe Gambar 58-65).
// Untuk mengganti isi halaman, cukup ubah file ini, lalu jalankan: php artisan config:clear
//
// 'gambar' = lokasi file di dalam folder public. Kalau file-nya belum ada,
// halaman menampilkan kotak beige berisi label sebagai pengganti.

return [
    'kontak' => [
        'whatsapp' => env('AURA_WHATSAPP', ''),
        'whatsappTampil' => env('AURA_WHATSAPP_TAMPIL', '+62 8xx-xxxx-xxxx'),
        'pesanWhatsapp' => 'Halo Aura Community, saya ingin berkonsultasi tentang kerja sama campaign.',
        'instagram' => 'auracreators.id',
        'email' => 'tobrightaura@gmail.com',
    ],

    'hero' => [
        'judulAwal' => 'Hubungkan Brand Anda dengan',
        'judulSorot' => 'Influencer yang Tepat',
        'deskripsi' => 'Aura Community membantu brand dan agency dalam menjalankan kebutuhan influencer marketing melalui pengelolaan dan kolaborasi dengan Influencer.',
        'gambar' => 'images/hero.jpg',
    ],

    'tentang' => [
        'paragrafUtama' => 'Aura Community merupakan bagian dari CV Aura Digital Kreatif yang bergerak dalam kebutuhan pemasaran melalui media sosial dan influencer marketing serta menjadi partner bagi brand dan agency dalam menjalankan campaign.',
        'paragrafKedua' => 'Aura Community memiliki data Influencer dengan jumlah lebih dari 3.000 Influencer dengan rentang followers sekitar 1.000 hingga 100.000 followers.',
        'gambar' => 'images/tentang.jpg',
        'statistik' => [
            ['nilai' => '3.000+', 'label' => 'Influencer Terdaftar'],
            ['nilai' => '1.000 – 100.000', 'label' => 'Rentang Followers'],
        ],
    ],

    'layanan' => [
        [
            'ikon' => 'phone',
            'judul' => 'Promosi melalui Konten Media Sosial',
            'deskripsi' => 'Promosi melalui pembuatan dan publikasi konten pada media sosial.',
        ],
        [
            'ikon' => 'geo-alt',
            'judul' => 'Visit',
            'deskripsi' => 'Kegiatan promosi melalui kunjungan Influencer ke lokasi yang ditentukan.',
        ],
    ],

    // Placeholder dari wireframe. Ganti dengan portofolio asli Aura Community.
    'portofolio' => [
        [
            'judul' => 'Franchise Kopi',
            'deskripsi' => 'Dokumentasi campaign promosi untuk franchise kopi.',
            'jenis' => 'Visit Campaign',
            'jumlahInfluencer' => 12,
            'gambar' => 'images/portofolio-kopi.jpg',
        ],
        [
            'judul' => 'Produk Kecantikan',
            'deskripsi' => 'Dokumentasi campaign promosi produk kecantikan.',
            'jenis' => 'Review & Endorsement',
            'jumlahInfluencer' => 25,
            'gambar' => 'images/portofolio-kecantikan.jpg',
        ],
        [
            'judul' => 'Promosi Digital',
            'deskripsi' => 'Dokumentasi kegiatan promosi digital melalui media sosial.',
            'jenis' => 'Aktivasi Konten',
            'jumlahInfluencer' => 18,
            'gambar' => 'images/portofolio-digital.jpg',
        ],
    ],

    // Placeholder dari wireframe. Ganti dengan testimoni asli dari klien Aura Community.
    'testimoni' => [
        [
        'nama' => 'Jessica Amanda',
        'perusahaan' => 'Beauty & Skincare Brand',
        'rating' => 5,
        'isi' => 'Senang bisa bekerja sama dengan Aura Community. Prosesnya mudah, komunikasinya lancar, dan influencer yang dipilih sesuai dengan kebutuhan brand kami.'
    ],
    [
        'nama' => 'Michelle Tan',
        'perusahaan' => 'Fashion & Lifestyle Brand',
        'rating' => 5,
        'isi' => 'Tim Aura Community sangat membantu dari awal sampai campaign berjalan. Semuanya terkoordinasi dengan baik dan responsnya cepat.'
    ],
    [
        'nama' => 'Andreas Wijaya',
        'perusahaan' => 'Local Consumer Brand',
        'rating' => 5,
        'isi' => 'Pengalaman kerja sama yang menyenangkan. Kami jadi lebih mudah menemukan influencer yang cocok tanpa harus mengurus semuanya sendiri.'
    ],
    ],

    // Galeri Konten: 3 GIF tegak 9:16 (format TikTok/Reels).
    // 'gambar' = lokasi file di folder public, misalnya images/galeri-1.gif
    // 'label'  = keterangan di bawah GIF
    // Kalau file belum ada, tampil kotak pengganti dengan ukuran yang sama.
    'galeri' => [
        ['gambar' => 'images/galeri-1.gif', 'label' => 'Hair Vitamin Ellips'],
        ['gambar' => 'images/galeri-2.gif', 'label' => 'Cologne Gel Eskulin'],
        ['gambar' => 'images/galeri-3.gif', 'label' => 'Body Care EZ WHITE'],
    ],

    'footer' => [
        'deskripsi' => 'Wadah eksplorasi struktural, kolaborasi gagasan, dan ekosistem kreatif publik terpadu.',
    ],
];