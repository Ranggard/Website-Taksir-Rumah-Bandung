@extends('layouts.app')
@section('title', 'Riwayat')

@section('content')
<div class="mx-auto max-w-4xl px-5 py-10" x-data="riwayat({{ \Illuminate\Support\Js::from($items->items()) }})">
    <h1 class="font-display text-3xl font-bold text-ink">Riwayat prediksi</h1>

    @if ($total === 0)
        <div class="card mt-6 text-center">
            <p class="font-semibold text-ink">Belum ada riwayat prediksi</p>
            <p class="mt-1 text-sm text-slate-600">Hitung estimasi pertama Anda, hasilnya akan muncul di sini.</p>
            <a href="{{ route('predict') }}" class="btn btn-primary mt-4">Mulai Prediksi</a>
        </div>
    @else
        {{-- Cari, filter, dan urutkan (dikirim lewat URL / GET) --}}
        <form method="GET" action="{{ route('history') }}" class="card mt-6 space-y-4 !p-4">
            <div class="flex gap-2">
                <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Cari alamat, deskripsi, sertifikat, atau furnishing">
                <button class="btn btn-primary shrink-0">Cari</button>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div><label class="label">Urutkan</label>
                    <select class="input" name="sort" onchange="this.form.submit()">
                        @foreach (['terbaru' => 'Terbaru', 'terlama' => 'Terlama', 'harga_tinggi' => 'Harga tertinggi', 'harga_rendah' => 'Harga terendah', 'tanah_besar' => 'Tanah terluas', 'alamat' => 'Alamat A–Z'] as $v => $t)
                            <option value="{{ $v }}" @selected(request('sort', 'terbaru') === $v)>{{ $t }}</option>
                        @endforeach
                    </select></div>
                <div><label class="label">Rentang harga</label>
                    <select class="input" name="harga" onchange="this.form.submit()">
                        @foreach (['all' => 'Semua harga', '0-1' => 'Di bawah Rp 1 M', '1-2' => 'Rp 1 M – 2 M', '2-4' => 'Rp 2 M – 4 M', '4-99' => 'Di atas Rp 4 M'] as $v => $t)
                            <option value="{{ $v }}" @selected(request('harga', 'all') === $v)>{{ $t }}</option>
                        @endforeach
                    </select></div>
                <div><label class="label">Kamar tidur</label>
                    <select class="input" name="kt" onchange="this.form.submit()">
                        <option value="0">Semua</option>
                        @foreach ([2, 3, 4, 5] as $n)
                            <option value="{{ $n }}" @selected((int) request('kt') === $n)>{{ $n }} atau lebih</option>
                        @endforeach
                    </select></div>
                <div><label class="label">Insight</label>
                    <select class="input" name="insight" onchange="this.form.submit()">
                        @foreach (['all' => 'Semua', 'ada' => 'Sudah tersedia', 'belum' => 'Belum tersedia'] as $v => $t)
                            <option value="{{ $v }}" @selected(request('insight', 'all') === $v)>{{ $t }}</option>
                        @endforeach
                    </select></div>
                <div><label class="label">Dari tanggal</label><input class="input" type="date" name="dari" value="{{ request('dari') }}" onchange="this.form.submit()"></div>
                <div><label class="label">Sampai tanggal</label><input class="input" type="date" name="sampai" value="{{ request('sampai') }}" onchange="this.form.submit()"></div>
                <div><label class="label">Tampil per halaman</label>
                    <select class="input" name="per" onchange="this.form.submit()">
                        @foreach ([5, 10, 20] as $n)
                            <option value="{{ $n }}" @selected((int) request('per', 5) === $n)>{{ $n }} baris</option>
                        @endforeach
                    </select></div>
                <div class="flex items-end"><a href="{{ route('history') }}" class="btn btn-ghost w-full">Reset filter</a></div>
            </div>
        </form>

        <p class="mt-4 text-sm text-slate-600">Menampilkan <b>{{ $items->total() }}</b> dari {{ $total }} riwayat</p>

        @if ($items->total() === 0)
            <div class="card mt-3 text-center">
                <p class="font-semibold text-ink">Tidak ada riwayat yang cocok</p>
                <p class="mt-1 text-sm text-slate-600">Ubah kata kunci atau longgarkan filter.</p>
                <a href="{{ route('history') }}" class="btn btn-ghost mt-4">Reset filter</a>
            </div>
        @else
            <ul class="mt-3 space-y-3">
                @foreach ($items as $it)
                    <li class="card flex flex-col gap-3 !p-4 md:flex-row md:items-center md:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs text-slate-500">{{ $it['tanggal'] }}</p>
                            <a href="{{ route('result', $it['id']) }}" class="block truncate font-semibold text-ink hover:underline">{{ $it['alamat'] }}</a>
                            <p class="text-sm text-slate-600">LT {{ $it['tanah'] }} m² · LB {{ $it['bangunan'] }} m² · {{ $it['kt'] }} KT · {{ $it['km'] }} KM · {{ $it['garasi'] }} garasi</p>
                        </div>
                        <div class="flex shrink-0 flex-col gap-2 md:items-end">
                            <p class="font-display text-lg font-bold text-pine">{{ $it['harga_ringkas'] }}</p>
                            <div class="flex gap-2 text-sm">
                                <button class="btn btn-ghost !px-3 !py-1.5" @click="insightId = {{ $it['id'] }}">Lihat Insight</button>
                                <button class="btn btn-ghost !px-3 !py-1.5" @click="$store.toast.show('Unduh PDF dibuat di tahap backend')">Unduh PDF</button>
                                <button class="btn btn-ghost !px-3 !py-1.5 !text-red-600" @click="hapusId = {{ $it['id'] }}">Hapus</button>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="mt-6">{{ $items->links() }}</div>
        @endif
    @endif

    {{-- Modal insight --}}
    <div x-show="insightId" x-cloak class="fixed inset-0 z-[1500] grid place-items-center bg-black/50 p-4" @keydown.escape.window="insightId = null">
        <div class="card max-h-[85vh] w-full max-w-lg overflow-y-auto" @click.outside="insightId = null">
            <h2 class="font-display text-lg font-bold text-ink">Insight tersimpan</h2>
            <template x-if="aktif && aktif.insight">
                <div class="mt-3 space-y-3 text-sm">
                    <template x-for="[k, j] in [['kelebihan','Kelebihan'],['kekurangan','Kekurangan'],['saran','Saran']]" :key="k">
                        <div><h3 class="font-semibold text-pine" x-text="j"></h3>
                            <ul class="list-disc pl-4"><template x-for="t in aktif.insight[k]"><li x-text="t"></li></template></ul></div>
                    </template>
                    <div class="rounded-lg bg-mist p-3"><b class="text-pine">Proyeksi investasi</b><p x-text="aktif.insight.proyeksi"></p></div>
                </div>
            </template>
            <p class="mt-3 text-sm text-slate-500" x-show="aktif && !aktif.insight">Insight belum tersedia.</p>
            <button class="btn btn-ghost mt-4" @click="insightId = null">Tutup</button>
        </div>
    </div>

    {{-- Dialog hapus --}}
    <div x-show="hapusId" x-cloak class="fixed inset-0 z-[1500] grid place-items-center bg-black/50 p-4">
        <form method="POST" :action="'{{ url('/riwayat') }}/' + hapusId" class="card w-full max-w-sm" @click.outside="hapusId = null">
            @csrf @method('DELETE')
            <h2 class="font-display text-lg font-bold text-ink">Hapus riwayat ini?</h2>
            <p class="mt-1 text-sm text-slate-600">Riwayat yang dihapus tidak bisa dikembalikan.</p>
            <div class="mt-4 flex gap-2">
                <button class="btn btn-danger">Hapus</button>
                <button type="button" class="btn btn-ghost" @click="hapusId = null">Batal</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function riwayat(items) {
    return {
        items, insightId: null, hapusId: null,
        get aktif() { return this.items.find(i => i.id === this.insightId); },
    };
}
</script>
@endpush
