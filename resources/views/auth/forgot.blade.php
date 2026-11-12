@extends('layouts.app')
@section('title', 'Lupa Password')

@section('content')
<div class="mx-auto max-w-md px-5 py-14" x-data="forgot()">
    <h1 class="font-display text-3xl font-bold text-ink">Lupa password</h1>

    <div class="card mt-6 space-y-4">
        {{-- Langkah 1: email --}}
        <div x-show="step === 1" class="space-y-4">
            <div>
                <label class="label" for="email">Email akun (@gmail.com)</label>
                <input class="input" id="email" type="email" x-model="email" @keydown.enter.prevent="kirim()">
            </div>
            <button type="button" class="btn btn-primary w-full" @click="kirim()">Kirim Kode</button>
        </div>

        {{-- Langkah 2: kode 6 digit --}}
        <div x-show="step === 2" x-cloak class="space-y-4">
            <p class="rounded-lg bg-mist px-3 py-2 text-sm" x-text="pesan"></p>
            <div>
                <label class="label" for="kode">Kode verifikasi (6 digit)</label>
                <input class="input tracking-[0.4em]" id="kode" inputmode="numeric" maxlength="6" x-model="kode" @keydown.enter.prevent="cek()">
            </div>
            <button type="button" class="btn btn-primary w-full" @click="cek()">Verifikasi</button>
            <button type="button" class="text-xs text-pine hover:underline" @click="kirim()">Kirim ulang kode</button>
        </div>

        {{-- Langkah 3: password baru --}}
        <form x-show="step === 3" x-cloak method="POST" action="{{ route('forgot.done') }}" class="space-y-4" @submit.prevent="simpan($event)">
            @csrf
            <div>
                <label class="label" for="pw">Password baru (minimal 8 karakter)</label>
                <input class="input" id="pw" type="password" name="password" x-model="pw">
            </div>
            <div>
                <label class="label" for="pw2">Konfirmasi password</label>
                <input class="input" id="pw2" type="password" name="password_confirmation" x-model="pw2">
            </div>
            <button class="btn btn-primary w-full">Simpan password</button>
        </form>

        <p class="err !mt-0 text-sm" x-show="err" x-text="err"></p>
    </div>
    <p class="mt-4 text-center text-sm"><a href="{{ route('login') }}" class="font-semibold text-pine hover:underline">Kembali ke Login</a></p>
</div>
@endsection

@push('scripts')
<script>
function forgot() {
    return {
        step: 1, email: '', kode: '', salah: 0, pesan: '', err: '', pw: '', pw2: '',
        kirim() {
            this.err = '';
            if (!/^[^@\s]+@gmail\.com$/i.test(this.email)) { this.err = 'Email harus berakhiran @gmail.com.'; return; }
            // DUMMY: nanti Laravel mengirim kode 6 digit (di-hash di database, berlaku 10 menit)
            this.pesan = 'Jika email terdaftar, kode sudah dikirim. (Dummy: gunakan 123456)';
            this.step = 2; this.salah = 0; this.kode = '';
        },
        cek() {
            this.err = '';
            if (this.kode === '123456') { this.step = 3; return; }
            this.salah++;
            if (this.salah >= 5) { this.step = 1; this.err = 'Kode dihanguskan karena salah 5 kali. Minta kode baru.'; return; }
            this.err = 'Kode salah atau kedaluwarsa. Sisa percobaan: ' + (5 - this.salah) + '.';
        },
        simpan(e) {
            this.err = '';
            if (this.pw.length < 8) { this.err = 'Password minimal 8 karakter.'; return; }
            if (this.pw !== this.pw2) { this.err = 'Konfirmasi password tidak sama.'; return; }
            e.target.submit();
        }
    }
}
</script>
@endpush
