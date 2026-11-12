<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') · Taksir Rumah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Instrument+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                colors: { ink: '#12262a', pine: '#1e5a4b', tea: '#6aa17f', mist: '#edf2ef', signal: '#e0a526' },
                fontFamily: { display: ['"Bricolage Grotesque"', 'sans-serif'], sans: ['"Instrument Sans"', 'sans-serif'] }
            } }
        }
    </script>
    @verbatim
    <style type="text/tailwindcss">
        @layer components {
            .input { @apply w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-pine focus:outline-none focus:ring-2 focus:ring-pine/30; }
            .label { @apply mb-1 block text-sm font-medium text-ink; }
            .btn { @apply inline-flex items-center justify-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-50; }
            .btn-primary { @apply bg-pine text-white hover:bg-ink; }
            .btn-signal { @apply bg-signal text-ink hover:brightness-95; }
            .btn-ghost { @apply border border-slate-300 bg-white text-ink hover:bg-mist; }
            .btn-danger { @apply bg-red-600 text-white hover:bg-red-700; }
            .card { @apply rounded-xl border border-slate-200 bg-white p-6; }
            .err { @apply mt-1 text-xs text-red-600; }
        }
    </style>
    @endverbatim
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    @stack('head')
</head>
<body class="min-h-screen bg-mist font-sans text-slate-800 antialiased">

@php $user = session('user'); @endphp

<header class="sticky top-0 z-[1000] border-b border-slate-200 bg-white/95 backdrop-blur">
    <nav class="mx-auto flex max-w-6xl items-center justify-between px-5 py-3" x-data="{ menu: false, profil: false }">
        <a href="{{ route('home') }}" class="flex items-center gap-2 font-display text-lg font-bold text-ink">
            <svg viewBox="0 0 32 32" class="h-7 w-7" fill="none" stroke="#1e5a4b" stroke-width="1.8" aria-hidden="true">
                <ellipse cx="16" cy="16" rx="13" ry="9"/><ellipse cx="16" cy="16" rx="8" ry="5"/><ellipse cx="16" cy="16" rx="3" ry="2" fill="#e0a526" stroke="none"/>
            </svg>
            Taksir Rumah
        </a>

        <button class="rounded-lg p-2 md:hidden" @click="menu = !menu" aria-label="Buka menu">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        @php
            $links = $user
                ? [['home','Beranda'],['predict','Prediksi'],['history','Riwayat'],['map','Peta']]
                : [['home','Beranda']];
        @endphp

        <div class="hidden items-center gap-1 md:flex">
            @foreach ($links as [$r, $t])
                <a href="{{ route($r) }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs($r) ? 'bg-mist text-pine' : 'text-slate-600 hover:text-ink' }}">{{ $t }}</a>
            @endforeach

            @if ($user)
                <div class="relative ml-2" @click.outside="profil = false">
                    <button @click="profil = !profil" class="flex items-center gap-2 rounded-full border border-slate-200 py-1 pl-1 pr-3 text-sm font-medium">
                        <span class="grid h-7 w-7 place-items-center rounded-full bg-pine text-xs text-white">{{ strtoupper(substr($user['name'], 0, 1)) }}</span>
                        {{ \Illuminate\Support\Str::limit($user['name'], 16) }}
                    </button>
                    <div x-show="profil" x-cloak class="absolute right-0 mt-2 w-44 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                        <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm hover:bg-mist">Edit Profil</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button class="block w-full px-4 py-2 text-left text-sm hover:bg-mist">Logout</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-ghost ml-2 !py-2">Login</a>
                <a href="{{ route('register') }}" class="btn btn-primary !py-2">Register</a>
            @endif
        </div>

        {{-- Menu mobile --}}
        <div x-show="menu" x-cloak class="absolute left-0 top-full w-full border-b border-slate-200 bg-white px-5 py-3 md:hidden">
            @foreach ($links as [$r, $t])
                <a href="{{ route($r) }}" class="block py-2 text-sm font-medium">{{ $t }}</a>
            @endforeach
            @if ($user)
                <a href="{{ route('profile') }}" class="block py-2 text-sm font-medium">Edit Profil</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="py-2 text-sm font-medium">Logout</button></form>
            @else
                <a href="{{ route('login') }}" class="block py-2 text-sm font-medium">Login</a>
                <a href="{{ route('register') }}" class="block py-2 text-sm font-medium">Register</a>
            @endif
        </div>
    </nav>
</header>

@foreach (['success' => 'bg-green-50 text-green-800 border-green-200', 'info' => 'bg-amber-50 text-amber-900 border-amber-200', 'error' => 'bg-red-50 text-red-800 border-red-200'] as $k => $cls)
    @if (session($k))
        <div class="mx-auto mt-4 max-w-6xl px-5">
            <div class="rounded-lg border px-4 py-3 text-sm {{ $cls }}" role="alert">{{ session($k) }}</div>
        </div>
    @endif
@endforeach

<main>@yield('content')</main>

<footer class="mt-16 border-t border-slate-200 py-6 text-center text-xs text-slate-500">
    Taksir Rumah · Estimasi harga rumah Bandung Raya · Proyek kuliah
</footer>

{{-- Toast sederhana --}}
<div x-data x-show="$store.toast.msg" x-cloak x-transition
     class="fixed bottom-5 left-1/2 z-[2000] -translate-x-1/2 rounded-lg bg-ink px-4 py-3 text-sm text-white shadow-lg"
     x-text="$store.toast.msg"></div>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('toast', { msg: '', show(m) { this.msg = m; setTimeout(() => this.msg = '', 2800); } });
    });
</script>
<style>[x-cloak]{display:none !important}</style>

@stack('scripts')
</body>
</html>
