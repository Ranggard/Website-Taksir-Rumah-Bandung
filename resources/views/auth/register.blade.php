@extends('layouts.app')
@section('title', 'Register')

@section('content')
<div class="mx-auto max-w-md px-5 py-14">
    <h1 class="font-display text-3xl font-bold text-ink">Buat akun baru</h1>

    <form method="POST" action="{{ route('register.store') }}" class="card mt-6 space-y-4">
        @csrf
        <div>
            <label class="label" for="name">Nama lengkap</label>
            <input class="input" id="name" name="name" value="{{ old('name') }}" required>
            @error('name')<p class="err">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label" for="email">Email (@gmail.com)</label>
            <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required>
            @error('email')<p class="err">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label" for="password">Password (minimal 8 karakter)</label>
            <input class="input" id="password" type="password" name="password" required>
            @error('password')<p class="err">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label" for="password_confirmation">Konfirmasi password</label>
            <input class="input" id="password_confirmation" type="password" name="password_confirmation" required>
        </div>
        <button class="btn btn-primary w-full">Daftar</button>
    </form>
    <p class="mt-4 text-center text-sm">Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-pine hover:underline">Login</a></p>
</div>
@endsection
