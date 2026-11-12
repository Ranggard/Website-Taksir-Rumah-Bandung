@extends('layouts.app')
@section('title', 'Beranda')

@section('content')
<section class="relative overflow-hidden bg-ink text-white">
    {{-- Garis kontur: simbol ketinggian (MDPL) --}}
    <svg class="absolute inset-0 h-full w-full opacity-40" viewBox="0 0 800 400" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
        <defs>
            <filter id="wobble" x="-50%" y="-50%" width="200%" height="200%">
                <feTurbulence type="fractalNoise" baseFrequency="0.008 0.012" numOctaves="2" seed="7" result="n"/>
                <feDisplacementMap in="SourceGraphic" in2="n" scale="70"/>
            </filter>
        </defs>
        <g fill="none" stroke="#6aa17f" stroke-width="1.2" filter="url(#wobble)" transform="translate(620 210)">
            @for ($i = 1; $i <= 14; $i++)
                <ellipse rx="{{ $i * 30 }}" ry="{{ $i * 19 }}"/>
            @endfor
        </g>
    </svg>
    <div class="relative mx-auto max-w-6xl px-5 py-20 md:py-28">
        <h1 class="max-w-2xl font-display text-4xl font-bold leading-tight md:text-6xl">Berapa harga rumah Anda di Bandung Raya?</h1>
        <p class="mt-5 max-w-xl text-lg text-slate-300">Isi data rumah dan alamat. Kami hitung estimasi harganya dengan memperhitungkan ketinggian lokasi, lalu menjelaskan kelebihan, kekurangan, dan proyeksi investasinya.</p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('predict') }}" class="btn btn-signal">Mulai Prediksi</a>
            <a href="{{ route('map') }}" class="btn border border-white/30 text-white hover:bg-white/10">Lihat peta sebaran harga</a>
        </div>
    </div>
</section>

<section class="mx-auto max-w-6xl px-5 py-14">
    <h2 class="font-display text-2xl font-bold text-ink">Cara memakainya</h2>
    <ol class="mt-6 grid gap-4 md:grid-cols-4">
        @foreach ([
            ['Isi data rumah', 'Luas tanah, luas bangunan, kamar tidur, kamar mandi, dan garasi.'],
            ['Cari lokasi', 'Ketik alamat, pilih saran, lalu ketinggian (MDPL) terisi otomatis.'],
            ['Dapatkan estimasi', 'Harga dihitung dari 8 data utama memakai model machine learning.'],
            ['Baca insight', 'Hasil tersimpan di riwayat dan bisa diunduh sebagai PDF.'],
        ] as $i => [$judul, $isi])
            <li class="card">
                <span class="grid h-8 w-8 place-items-center rounded-full bg-pine text-sm font-semibold text-white">{{ $i + 1 }}</span>
                <h3 class="mt-3 font-display text-lg font-bold text-ink">{{ $judul }}</h3>
                <p class="mt-1 text-sm text-slate-600">{{ $isi }}</p>
            </li>
        @endforeach
    </ol>
</section>
@endsection
