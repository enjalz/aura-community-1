<?php

// Menu sidebar per peran (wireframe Gambar 68, 82, 87).
// 'route' = nama route tujuan. 'ikon' = nama ikon Bootstrap Icons tanpa awalan "bi-".
// 'selalu' = tetap bisa dibuka walaupun akun influencer belum diverifikasi.
return [
    'admin' => [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'ikon' => 'grid'],
        ['grup' => 'Pengguna'],
        ['label' => 'Verifikasi Akun', 'route' => 'admin.verifikasi', 'ikon' => 'person-check'],
        ['label' => 'Data Influencer', 'route' => 'admin.influencer', 'ikon' => 'people'],
        ['label' => 'Data Owner Brand', 'route' => 'admin.owner-brand', 'ikon' => 'shop'],
        ['grup' => 'Campaign'],
        ['label' => 'Campaign', 'route' => 'admin.campaign', 'ikon' => 'megaphone'],
        ['label' => 'Hasil Campaign', 'route' => 'admin.hasil', 'ikon' => 'file-earmark-play'],
        ['label' => 'Verifikasi Insight', 'route' => 'admin.insight', 'ikon' => 'bar-chart'],
        ['label' => 'Pembayaran', 'route' => 'admin.pembayaran', 'ikon' => 'wallet2'],
        ['label' => 'Laporan Hasil Campaign', 'route' => 'admin.laporan', 'ikon' => 'clipboard-data'],
        ['label' => 'Riwayat Campaign', 'route' => 'admin.riwayat', 'ikon' => 'clock-history'],
        ['grup' => 'Akun'],
        ['label' => 'Profil', 'route' => 'admin.profil', 'ikon' => 'person-circle'],
    ],

    'owner_brand' => [
        ['label' => 'Dashboard', 'route' => 'owner.dashboard', 'ikon' => 'grid'],
        ['grup' => 'Campaign'],
        ['label' => 'Campaign', 'route' => 'owner.campaign', 'ikon' => 'megaphone'],
        ['label' => 'Laporan Hasil Campaign', 'route' => 'owner.laporan', 'ikon' => 'clipboard-data'],
        ['label' => 'Riwayat Campaign', 'route' => 'owner.riwayat', 'ikon' => 'clock-history'],
        ['grup' => 'Akun'],
        ['label' => 'Profil', 'route' => 'owner.profil', 'ikon' => 'person-circle'],
    ],

    'influencer' => [
        ['label' => 'Dashboard', 'route' => 'influencer.dashboard', 'ikon' => 'grid'],
        ['grup' => 'Campaign'],
        ['label' => 'Campaign', 'route' => 'influencer.campaign', 'ikon' => 'megaphone'],
        ['label' => 'Hasil Pengerjaan', 'route' => 'influencer.hasil', 'ikon' => 'file-earmark-play'],
        ['label' => 'Riwayat Campaign', 'route' => 'influencer.riwayat', 'ikon' => 'clock-history'],
        ['grup' => 'Akun'],
        ['label' => 'Status Verifikasi', 'route' => 'influencer.status', 'ikon' => 'shield-check', 'selalu' => true],
        ['label' => 'Profil', 'route' => 'influencer.profil', 'ikon' => 'person-circle'],
    ],
];