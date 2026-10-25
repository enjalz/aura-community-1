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
        'instagram' => 'auracommunity',
        'email' => 'halo@auracommunity.id',
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
        ['nama' => 'Nama Client', 'perusahaan' => 'Perusahaan / Brand', 'rating' => 5, 'isi' => 'Testimoni client akan ditampilkan di sini.'],
        ['nama' => 'Nama Client', 'perusahaan' => 'Perusahaan / Brand', 'rating' => 5, 'isi' => 'Testimoni client akan ditampilkan di sini.'],
        ['nama' => 'Nama Client', 'perusahaan' => 'Perusahaan / Brand', 'rating' => 5, 'isi' => 'Testimoni client akan ditampilkan di sini.'],
    ],

    'galeri' => [
        'images/galeri-1.jpg',
        'images/galeri-2.jpg',
        'images/galeri-3.jpg',
        'images/galeri-4.jpg',
        'images/galeri-5.jpg',
        'images/galeri-6.jpg',
    ],

    'footer' => [
        'deskripsi' => 'Wadah eksplorasi struktural, kolaborasi gagasan, dan ekosistem kreatif publik terpadu.',
    ],
];