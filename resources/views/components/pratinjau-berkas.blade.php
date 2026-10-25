{{-- Pratinjau berkas draft (video atau gambar) atau tangkapan layar insight.
     - Kotak kecil mengikuti bentuk asli berkas (tegak 9:16 maupun mendatar), dengan tinggi maksimal.
     - Tombol "Perbesar" membuka berkas di jendela besar (modal Bootstrap).
     Berkas dibuka lewat route berkas.hasil (BerkasController), bukan langsung dari folder.
     Contoh: <x-pratinjau-berkas :hasil="$hasil" jenis="draft" /> --}}
@props(['hasil', 'jenis'])

@php
    $lokasi = $jenis === 'draft' ? $hasil->draftKonten : $hasil->fileInsight;
    $ada = $lokasi && \Illuminate\Support\Facades\Storage::disk('local')->exists($lokasi);
    $ekstensi = strtolower(pathinfo((string) $lokasi, PATHINFO_EXTENSION));
    $video = in_array($ekstensi, ['mp4', 'mov', 'webm'], true);
    $alamat = route('berkas.hasil', [$hasil, $jenis]);
    $judul = $jenis === 'draft' ? 'Draft konten' : 'Tangkapan layar insight';

    // id unik untuk modal, supaya tidak bentrok kalau ada lebih dari satu pratinjau di halaman
    $idModal = 'pratinjau-' . $jenis . '-' . $hasil->idHasil;
@endphp

@if (! $ada)
    <div class="pratinjau pratinjau-kosong">
        <span class="teks-redup">Berkas tidak tersedia</span>
    </div>
@else
    <div class="pratinjau">
        @if ($video)
            {{-- preload="metadata": browser hanya mengambil ukuran dan durasi dulu, bukan seluruh video --}}
            <video src="{{ $alamat }}" controls preload="metadata"></video>
        @else
            <img src="{{ $alamat }}" alt="{{ $judul }}">
        @endif

        <button type="button" class="tombol-perbesar" data-bs-toggle="modal" data-bs-target="#{{ $idModal }}">
            <i class="bi bi-arrows-fullscreen" aria-hidden="true"></i>
            Perbesar
        </button>
    </div>

    {{-- Jendela besar. Bootstrap membuka dan menutupnya lewat data-bs-toggle di tombol di atas. --}}
    <div class="modal fade modal-pratinjau" id="{{ $idModal }}" tabindex="-1" aria-label="{{ $judul }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">{{ $judul }}</h2>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    @if ($video)
                        <video src="{{ $alamat }}" controls preload="none"></video>
                    @else
                        <img src="{{ $alamat }}" alt="{{ $judul }}">
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- @once: script ini hanya dimuat sekali walaupun ada beberapa pratinjau di satu halaman --}}
    @once
        @push('scripts')
            <script src="{{ asset('js/pratinjau.js') }}"></script>
        @endpush
    @endonce
@endif