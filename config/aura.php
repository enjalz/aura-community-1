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

    // Syarat bergabung: setiap akun media sosial yang didaftarkan minimal punya pengikut sebanyak ini
    'minimalPengikut' => 1000,

    // Jenis konten di ketentuan platform (Tabel 34)
    'jenisKonten' => [
        'video' => 'Video',
        'foto' => 'Foto',
        'story' => 'Story',
    ],

    // Pilihan Lokasi Audiens (Negara) di form demografi.
    // Nilai disimpan apa adanya di kolom AkunSosmed.domisiliAudiens.
    'negaraAudiens' => [
        'Indonesia',
        'Malaysia',
        'Singapura',
        'Thailand',
        'Filipina',
        'Vietnam',
        'Brunei Darussalam',
        'Amerika Serikat',
        'Lainnya',
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
        'draft' => [
            'ekstensi'      => 'mp4,mov,webm,jpg,jpeg,png',
            'maksimal'      => 102400,  // 100 MB untuk video
            'maksimalFoto'  => 10240,   // 10 MB untuk foto
        ],
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

    // Penyebab pembayaran gagal: kode dari Xendit => keterangan untuk Admin.
    // Kode yang tidak ada di daftar ini ditampilkan sebagai "Transfer gagal diproses".
    'penyebabGagal' => [
        'INVALID_DESTINATION' => 'Rekening tidak valid',
        'REJECTED_BY_CHANNEL' => 'Ditolak oleh bank/e-wallet tujuan',
        'DESTINATION_MAXIMUM_LIMIT' => 'Saldo e-wallet tujuan sudah penuh',
        'PENYEDIA_TIDAK_DIDUKUNG' => 'Bank/e-wallet belum didukung',
        'INSUFFICIENT_BALANCE' => 'Saldo Xendit tidak cukup',
        'TEMPORARY_TRANSFER_ERROR' => 'Gangguan sementara di bank tujuan',
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

    // Panjang nomor rekening bank yang umum dipakai: nama penyedia => [minimal, maksimal] digit.
    // Dipakai untuk mencegah salah ketik. Kalau ada nomor asli yang ditolak, sesuaikan angkanya di sini.
    // E-wallet tidak diatur di sini karena nomornya adalah nomor HP (diawali 08, 10 sampai 13 digit).
    'panjangRekening' => [
        'BCA' => [10, 10],
        'BRI' => [15, 15],
        'BNI' => [10, 10],
        'Mandiri' => [13, 13],
        'Permata' => [10, 16],
        'CIMB Niaga' => [12, 14],
    ],

    // Nomor simulasi Xendit mode Test (lihat README). Tetap diizinkan walaupun panjangnya tidak sesuai,
    // tetapi hanya selama XENDIT_SECRET_KEY masih kunci Test (diawali xnd_development_).
    'nomorSimulasiXendit' => ['121212', '999999'],

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

    // Jumlah hari menunggu setelah link konten dikirim sebelum insight boleh dikirim.
    // Insight media sosial baru lengkap setelah konten tayang beberapa hari.
    'hariTungguInsight' => 7,

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