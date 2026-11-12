@extends('layouts.app')
@section('title', 'Hasil Prediksi')

@section('content')
<div class="mx-auto max-w-4xl space-y-6 px-5 py-10">
    <section class="rounded-2xl bg-ink p-8 text-white">
        <p class="text-sm text-slate-300">Estimasi harga rumah</p>
        <p class="mt-1 font-display text-4xl font-bold md:text-5xl">{{ $item['harga_fmt'] }}</p>
        <p class="mt-2 text-sm text-slate-300">{{ $item['alamat'] }} · {{ $item['tanggal'] }}</p>
    </section>

    <section class="card">
        <h2 class="font-display text-lg font-bold text-ink">Ringkasan input</h2>
        <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-3 text-sm md:grid-cols-3">
            @foreach ([
                'Luas tanah' => $item['tanah'] . ' m²', 'Luas bangunan' => $item['bangunan'] . ' m²',
                'Kamar tidur' => $item['kt'], 'Kamar mandi' => $item['km'], 'Garasi' => $item['garasi'],
                'MDPL' => $item['mdpl'] . ' m', 'Latitude' => $item['lat'], 'Longitude' => $item['lng'],
                'Sertifikat' => $item['sertifikat'] ?? '-', 'Daya listrik' => $item['listrik'] ? $item['listrik'] . ' VA' : '-',
                'Furnishing' => $item['furnishing'] ?? '-',
            ] as $k => $v)
                <div><dt class="text-slate-500">{{ $k }}</dt><dd class="font-semibold text-ink">{{ $v }}</dd></div>
            @endforeach
        </dl>
        @if ($item['deskripsi'])
            <div class="mt-4 border-t border-slate-200 pt-4 text-sm">
                <p class="text-slate-500">Deskripsi rumah</p>
                <p class="mt-1 text-ink">{{ $item['deskripsi'] }}</p>
            </div>
        @endif
    </section>

    @if ($item['tanah'] > 500 || $item['bangunan'] > 400)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <b>Di luar rentang data pelatihan.</b> Ukuran rumah ini lebih besar dari mayoritas data model, jadi estimasinya kurang akurat.
        </div>
    @endif

    <section class="card">
        <h2 class="font-display text-lg font-bold text-ink">Insight dari Gemini</h2>
        @if ($item['insight'])
            <div class="mt-4 grid gap-5 md:grid-cols-3">
                @foreach (['kelebihan' => 'Kelebihan', 'kekurangan' => 'Kekurangan', 'saran' => 'Saran'] as $key => $judul)
                    <div>
                        <h3 class="text-sm font-semibold text-pine">{{ $judul }}</h3>
                        <ul class="mt-2 list-disc space-y-1.5 pl-4 text-sm">
                            @foreach ($item['insight'][$key] as $t)<li>{{ $t }}</li>@endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
            <div class="mt-5 rounded-lg bg-mist p-4 text-sm">
                <h3 class="font-semibold text-pine">Proyeksi investasi</h3>
                <p class="mt-1">{{ $item['insight']['proyeksi'] }}</p>
            </div>
        @else
            <p class="mt-3 text-sm text-slate-500">Insight belum tersedia.</p>
        @endif
    </section>

    <p class="text-xs text-slate-500">Estimasi dan proyeksi hanya perkiraan, bukan nasihat keuangan. Harga dihitung dari 8 data utama (luas tanah, luas bangunan, kamar tidur, kamar mandi, garasi, latitude, longitude, MDPL). Sertifikat, daya listrik, furnishing, dan deskripsi hanya dipakai untuk analisis.</p>

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('predict') }}" class="btn btn-primary">Prediksi Lagi</a>
        <a href="{{ route('history') }}" class="btn btn-ghost">Lihat Riwayat</a>
    </div>
</div>
@endsection
