{{-- Paginasi sesuai wireframe: "Menampilkan 1-5 dari 24 akun" + Sebelumnya / nomor / Berikutnya.
     Dipanggil dari view dengan: {{ $daftar->links('partials.paginasi', ['label' => 'akun']) }}
     Laravel otomatis mengirim variabel $paginator ke file ini. --}}
@if ($paginator->total() > 0)
    <nav class="paginasi" aria-label="Navigasi halaman">
        <span>
            Menampilkan {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }}
            dari {{ $paginator->total() }} {{ $label ?? 'data' }}
        </span>

        <ul class="paginasi-daftar">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="paginasi-item is-nonaktif">Sebelumnya</span>
                @else
                    <a class="paginasi-item" href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>
                @endif
            </li>

            @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $halaman => $url)
                <li>
                    @if ($halaman === $paginator->currentPage())
                        <span class="paginasi-item is-aktif" aria-current="page">{{ $halaman }}</span>
                    @else
                        <a class="paginasi-item" href="{{ $url }}">{{ $halaman }}</a>
                    @endif
                </li>
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a class="paginasi-item" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>
                @else
                    <span class="paginasi-item is-nonaktif">Berikutnya</span>
                @endif
            </li>
        </ul>
    </nav>
@endif