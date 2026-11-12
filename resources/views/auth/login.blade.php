@extends('layouts.app')
@section('title', 'Login')

@section('content')
<div class="mx-auto max-w-md px-5 py-14">
    <h1 class="font-display text-3xl font-bold text-ink">Masuk ke akun Anda</h1>
    <p class="mt-1 text-sm text-slate-600">Akun demo: <b>demo@gmail.com</b> / <b>password123</b></p>

    <form method="POST" action="{{ route('login.store') }}" class="card mt-6 space-y-4">
        @csrf
        @error('login')
            <div class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</div>
        @enderror
        <div>
            <label class="label" for="email">Email</label>
            <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>
        <div x-data="{ lihat: false }">
            <label class="label" for="password">Password</label>
            <div class="relative">
                <input class="input pr-28" id="password" :type="lihat ? 'text' : 'password'" name="password" required>
                <button type="button" class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-pine hover:underline"
                        @click="lihat = !lihat" :aria-pressed="lihat" x-text="lihat ? 'Sembunyikan' : 'Tampilkan'"></button>
            </div>
            <a href="{{ route('forgot') }}" class="mt-1 inline-block text-xs text-pine hover:underline">Lupa password?</a>
        </div>
        <button class="btn btn-primary w-full">Login</button>
    </form>
    <p class="mt-4 text-center text-sm">Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-pine hover:underline">Daftar</a></p>
</div>
@endsection
