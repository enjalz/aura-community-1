{{-- Lonceng notifikasi di topbar. Dipanggil dari layouts/dashboard, memakai $pengguna dari layout.
     Titik merah = jumlah notifikasi belum dibaca. Isi dropdown = 20 notifikasi terbaru. --}}
@php
    // Influencer: cek dulu apakah ada insight yang sudah boleh dikirim (masa tunggu sudah lewat)
    if ($pengguna->peran() === 'influencer') {
        app(\App\Services\PengingatInsight::class)->jalankan($pengguna->idPengguna);
    }

    $daftarNotifikasi = $pengguna->notifikasi()->orderByDesc('idNotifikasi')->limit(20)->get();
    $jumlahBelumDibaca = $pengguna->notifikasi()->where('sudahDibaca', false)->count();
@endphp

<div class="dropdown notif">
    <button type="button" class="notif-tombol" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
            aria-label="Notifikasi, {{ $jumlahBelumDibaca }} belum dibaca">
        <i class="bi bi-bell" aria-hidden="true"></i>
        @if ($jumlahBelumDibaca > 0)
            <span class="notif-titik">{{ $jumlahBelumDibaca > 9 ? '9+' : $jumlahBelumDibaca }}</span>
        @endif
    </button>

    <div class="dropdown-menu dropdown-menu-end notif-panel">
        <div class="notif-kepala">
            <strong>Notifikasi</strong>
            @if ($jumlahBelumDibaca > 0)
                <form method="POST" action="{{ route('notifikasi.dibaca-semua') }}">
                    @csrf
                    <button type="submit" class="notif-tautan">Tandai semua dibaca</button>
                </form>
            @endif
        </div>

        <div class="notif-daftar">
            @forelse ($daftarNotifikasi as $notif)
                <div @class(['notif-item', 'is-baru' => ! $notif->sudahDibaca])>
                    <a href="{{ route('notifikasi.buka', $notif) }}" class="notif-isi">
                        <span class="notif-penanda" aria-hidden="true"></span>
                        <span class="notif-teks">
                            <span class="notif-judul">{{ $notif->judul }}</span>
                            @if ($notif->pesan)
                                <span class="notif-pesan">{{ $notif->pesan }}</span>
                            @endif
                            <span class="notif-waktu">{{ $notif->waktuDibuat->locale('id')->diffForHumans() }}</span>
                        </span>
                    </a>

                    <div class="notif-aksi">
                        @unless ($notif->sudahDibaca)
                            <form method="POST" action="{{ route('notifikasi.dibaca', $notif) }}">
                                @csrf
                                <button type="submit" title="Tandai dibaca" aria-label="Tandai dibaca">
                                    <i class="bi bi-check2" aria-hidden="true"></i>
                                </button>
                            </form>
                        @endunless
                        <form method="POST" action="{{ route('notifikasi.hapus', $notif) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Hapus" aria-label="Hapus notifikasi">
                                <i class="bi bi-trash3" aria-hidden="true"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="notif-kosong">Belum ada notifikasi.</p>
            @endforelse
        </div>
    </div>
</div>