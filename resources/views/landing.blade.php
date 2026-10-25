{{-- Landing page untuk Guest (use case Melihat Landing Page, Tabel 4).
     Setiap bagian dipisah ke folder resources/views/landing/ supaya mudah dicari dan diubah. --}}
@extends('layouts.app')

@section('title', 'Aura Community - Partner Influencer Marketing')

@push('styles')
    <link href="{{ asset('css/landing.css') }}" rel="stylesheet">
@endpush

{{-- landing.js: navbar menempel, menu aktif mengikuti posisi gulir, dan animasi muncul --}}
@push('scripts')
    <script src="{{ asset('js/landing.js') }}"></script>
@endpush

@section('content')
    @php
        // Semua teks dari config/landing.php. Variabel ini otomatis bisa dipakai oleh semua @include di bawah.
        $landing = config('landing');

        // Tautan WhatsApp dipakai di hero, kontak, dan footer, jadi dihitung sekali di sini
        $nomorWhatsapp = $landing['kontak']['whatsapp'];
        $linkWhatsapp = $nomorWhatsapp
            ? 'https://wa.me/' . $nomorWhatsapp . '?text=' . rawurlencode($landing['kontak']['pesanWhatsapp'])
            : '#kontak';
    @endphp

    @include('landing.navbar')

    <main>
        @include('landing.hero')
        @include('landing.tentang')
        @include('landing.layanan')
        @include('landing.portofolio')
        @include('landing.testimoni')
        @include('landing.galeri')
        @include('landing.kontak')
    </main>

    @include('landing.footer')
@endsection