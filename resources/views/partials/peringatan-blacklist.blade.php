{{-- Banner status blacklist untuk Influencer yang sedang login.
     Dipakai di halaman Campaign dan Hasil Pengerjaan. --}}
@php($influencerLogin = auth()->user()?->influencer)

@if ($influencerLogin?->statusBlacklist)
    <div class="alert-aura alert-gagal mb-3" role="alert">
        <strong><i class="bi bi-slash-circle" aria-hidden="true"></i> Akun Anda berstatus blacklist</strong>
        @if ($influencerLogin->tanggalBlacklist)
            <p class="mb-0">Sejak {{ $influencerLogin->tanggalBlacklist->format('d/m/Y') }}.</p>
        @endif
        @if ($influencerLogin->alasanBlacklistTampil())
            <p class="mb-0">Alasan: {{ $influencerLogin->alasanBlacklistTampil() }}</p>
        @endif
        <p class="mb-0">
            Anda tidak dapat mendaftar campaign baru. Campaign yang sedang berjalan tetap dapat diselesaikan,
            dan fee untuk pekerjaan yang sudah disetujui tetap dibayarkan. Hubungi Admin Aura Community jika ada pertanyaan.
        </p>
    </div>
@endif