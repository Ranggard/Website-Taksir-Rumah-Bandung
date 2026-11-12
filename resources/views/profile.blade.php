@extends('layouts.app')
@section('title', 'Edit Profil')

@section('content')
<div class="mx-auto max-w-3xl px-5 py-10">
    <h1 class="font-display text-3xl font-bold text-ink">Edit profil</h1>

    <form method="POST" action="{{ route('profile.update') }}" class="card mt-6">
        @csrf @method('PUT')
        <div class="grid gap-8 md:grid-cols-2 md:divide-x md:divide-slate-200">
            {{-- Kiri: data akun --}}
            <div class="space-y-4 md:pr-8">
                <h2 class="font-display text-lg font-bold text-ink">Data akun</h2>
                <div>
                    <label class="label" for="name">Nama lengkap</label>
                    <input class="input" id="name" name="name" value="{{ old('name', session('user.name')) }}">
                    @error('name')<p class="err">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="email">Email (@gmail.com)</label>
                    <input class="input" id="email" type="email" name="email" value="{{ old('email', session('user.email')) }}">
                    @error('email')<p class="err">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Kanan: ubah password (opsional) --}}
            <div class="space-y-4 md:pl-8">
                <div>
                    <h2 class="font-display text-lg font-bold text-ink">Ubah password</h2>
                    <p class="text-xs text-slate-500">Kosongkan jika tidak ingin mengubah password.</p>
                </div>
                <div>
                    <label class="label" for="password_lama">Password lama</label>
                    <input class="input" id="password_lama" type="password" name="password_lama" autocomplete="current-password">
                    @error('password_lama')<p class="err">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="password_baru">Password baru (minimal 8 karakter)</label>
                    <input class="input" id="password_baru" type="password" name="password_baru" autocomplete="new-password">
                    @error('password_baru')<p class="err">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="password_baru_confirmation">Konfirmasi password baru</label>
                    <input class="input" id="password_baru_confirmation" type="password" name="password_baru_confirmation" autocomplete="new-password">
                </div>
            </div>
        </div>

        <div class="mt-8 border-t border-slate-200 pt-5">
            <button class="btn btn-primary">Simpan perubahan</button>
        </div>
    </form>
</div>
@endsection
