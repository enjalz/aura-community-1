<?php

// Pilihan tetap yang dipakai di banyak halaman.
return [
    // Kategori (niche) influencer (Tabel 30)
    'kategori' => ['Beauty', 'Lifestyle', 'Fashion', 'Food', 'Travel', 'Parenting', 'Health', 'Tech', 'Gaming', 'Education'],

    // Platform media sosial: kunci field di formulir => nama yang disimpan di database
    'platform' => [
        'instagram' => 'Instagram',
        'tiktok' => 'TikTok',
    ],

    // Jenis konten di ketentuan platform (Tabel 34)
    'jenisKonten' => [
        'video' => 'Video',
        'foto' => 'Foto',
        'story' => 'Story',
    ],

    // Status campaign (Tabel 33)
    'statusCampaign' => [
        'draft' => 'Draft',
        'berjalan' => 'Berjalan',
        'selesai' => 'Selesai',
    ],

    // Status pendaftaran campaign (Tabel 37)
    'statusPendaftaran' => [
        'menunggu' => 'Menunggu',
        'diterima' => 'Diterima',
        'ditolak' => 'Ditolak',
    ],

    // Status pemeriksaan draft (Tabel 38)
    'statusDraft' => [
        'menunggu' => 'Menunggu',
        'revisi' => 'Revisi',
        'disetujui' => 'Disetujui',
    ],

    // Status pemeriksaan insight (Tabel 38)
    'statusInsight' => [
        'belum_dikirim' => 'Belum Dikirim',
        'menunggu' => 'Menunggu',
        'ditolak' => 'Ditolak',
        'disetujui' => 'Disetujui',
    ],

    // Berkas yang boleh diunggah (ukuran dalam kilobyte)
    'berkas' => [
        'draft' => ['ekstensi' => 'mp4,mov,webm,jpg,jpeg,png', 'maksimal' => 102400],  // 100 MB
        'insight' => ['ekstensi' => 'jpg,jpeg,png,webp', 'maksimal' => 5120],          // 5 MB
        'demografi' => ['ekstensi' => 'jpg,jpeg,png,webp', 'maksimal' => 5120],        // 5 MB
    ],

    // Status pembayaran (Tabel 40). "belum" = belum ada baris Pembayaran (kardinalitas 0..1).
    'statusPembayaran' => [
        'belum' => 'Belum Dibayar',
        'pending' => 'Pending',
        'berhasil' => 'Berhasil',
        'gagal' => 'Gagal',
    ],

    // Penyedia rekening yang bisa dipilih influencer => kode kanal Xendit Payouts.
    // ID_BCA terkonfirmasi dari contoh dokumentasi Xendit. Kode lain mengikuti pola yang sama;
    // kalau Xendit membalas CHANNEL_CODE_NOT_SUPPORTED, sesuaikan dengan daftar kanal di dashboard Xendit.
    'kanalPembayaran' => [
        'Bank' => [
            'BCA' => 'ID_BCA',
            'BRI' => 'ID_BRI',
            'BNI' => 'ID_BNI',
            'Mandiri' => 'ID_MANDIRI',
            'Permata' => 'ID_PERMATA',
            'CIMB Niaga' => 'ID_CIMB',
        ],
        'E-Wallet' => [
            'GoPay' => 'ID_GOPAY',
            'OVO' => 'ID_OVO',
            'DANA' => 'ID_DANA',
            'ShopeePay' => 'ID_SHOPEEPAY',
            'LinkAja' => 'ID_LINKAJA',
        ],
    ],

    'statusLaporan' => [
        'belum' => 'Belum Dibuat',
        'draft' => 'Draft',
        'final' => 'Final',
    ],

    // Rentang usia audiens (AkunSosmed.usiaDominan dan Campaign.targetUsia)
    'rentangUsia' => [
        '13-17' => '13–17 tahun',
        '18-24' => '18–24 tahun',
        '25-34' => '25–34 tahun',
        '35-44' => '35–44 tahun',
        '45+'   => '45 tahun ke atas',
    ],

    // Gender audiens dominan (AkunSosmed.genderDominan)
    'genderAudiens' => [
        'pria'   => 'Pria',
        'wanita' => 'Wanita',
    ],

    // Seleksi influencer (subbab 2.1.4 Manajemen Influencer)
    'seleksi' => [
        // Label "perlu diperiksa" jika view rate < 50% rata-rata rentang pengikutnya
        'ambangViewRate' => 50,

        // Rentang pengikut mengikuti Irwanda dkk.: nama => [minimal, maksimal]
        'rentangPengikut' => [
            '< 5K'      => [0, 4999],
            '5K–20K'    => [5000, 19999],
            '20K–100K'  => [20000, 99999],
            '100K–300K' => [100000, 299999],
            '> 300K'    => [300000, null],
        ],
    ],
];