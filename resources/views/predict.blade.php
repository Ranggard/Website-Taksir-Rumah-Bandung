@extends('layouts.app')
@section('title', 'Prediksi Harga')

@push('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">
@endpush

@section('content')
<div class="mx-auto max-w-6xl px-5 py-10" x-data="predictForm()" x-init="init()">
    <h1 class="font-display text-3xl font-bold text-ink">Prediksi harga rumah</h1>
    <p class="mt-1 text-sm text-slate-600">Kolom bertanda * wajib diisi.</p>

    <form method="POST" action="{{ route('predict.store') }}" class="mt-6 grid gap-6 lg:grid-cols-2"
          @submit.prevent="processing = true; setTimeout(() => $el.submit(), 1500)">
        @csrf
        <div class="space-y-6">
            <section class="card space-y-4">
                <h2 class="font-display text-lg font-bold text-ink">Data rumah</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="label">Luas tanah (m²) *</label><input class="input" type="number" min="1" name="tanah" x-model="f.tanah"></div>
                    <div><label class="label">Luas bangunan (m²) *</label><input class="input" type="number" min="1" name="bangunan" x-model="f.bangunan"></div>
                    <div><label class="label">Kamar tidur *</label><input class="input" type="number" min="1" name="kt" x-model="f.kt"></div>
                    <div><label class="label">Kamar mandi *</label><input class="input" type="number" min="1" name="km" x-model="f.km"></div>
                    <div><label class="label">Garasi (boleh 0) *</label><input class="input" type="number" min="0" name="garasi" x-model="f.garasi"></div>
                </div>
            </section>

            <section class="card space-y-4">
                <div>
                    <h2 class="font-display text-lg font-bold text-ink">Detail tambahan (opsional)</h2>
                    <p class="text-xs text-slate-500">Hanya dipakai untuk analisis insight, tidak memengaruhi angka harga.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div><label class="label">Sertifikat</label>
                        <select class="input" name="sertifikat"><option value="">Tidak diisi</option>
                            @foreach (['SHM', 'HGB', 'Girik', 'Lainnya'] as $o)<option>{{ $o }}</option>@endforeach</select></div>
                    <div><label class="label">Daya listrik (VA)</label>
                        <select class="input" name="listrik"><option value="">Tidak diisi</option>
                            @foreach ([900, 1300, 2200, 3500, 4400, 5500] as $o)<option>{{ $o }}</option>@endforeach</select></div>
                    <div><label class="label">Furnishing</label>
                        <select class="input" name="furnishing"><option value="">Tidak diisi</option>
                            @foreach (['Tanpa furnitur', 'Semi furnished', 'Fully furnished'] as $o)<option>{{ $o }}</option>@endforeach</select></div>
                </div>
                <div>
                    <div class="flex items-center justify-between">
                        <label class="label !mb-0" for="deskripsi">Deskripsi rumah</label>
                        <button type="button" class="text-xs font-semibold text-pine hover:underline" @click="f.deskripsi = contoh">Pakai contoh teks</button>
                    </div>
                    <textarea class="input mt-1" id="deskripsi" name="deskripsi" rows="5" maxlength="1000" x-model="f.deskripsi"
                              placeholder="Contoh: Rumah 2 lantai dalam cluster one gate system. Ada taman depan dan belakang, carport 2 mobil, dapur terpisah, dan kolam ikan kecil. Dekat sekolah, minimarket, dan akses tol. Bebas banjir dan kondisi bangunan terawat."></textarea>
                    <p class="mt-1 flex justify-between text-xs text-slate-500">
                        <span>Tulis fasilitas, kondisi bangunan, dan lingkungan sekitar.</span>
                        <span x-text="f.deskripsi.length + '/1000'"></span>
                    </p>
                </div>
            </section>
        </div>

        <section class="card space-y-4">
            <h2 class="font-display text-lg font-bold text-ink">Lokasi</h2>
            <div>
                <label class="label" for="alamat">Alamat lengkap *</label>
                <div class="flex gap-2">
                    <input class="input" id="alamat" x-model="alamat" @input="reset()" @keydown.enter.prevent="cariLokasi()" placeholder="Contoh: Dago, Antapani, Buah Batu">
                    <button type="button" class="btn btn-ghost shrink-0" @click="cariLokasi()" :disabled="alamat.trim().length < 3">Cari Lokasi</button>
                </div>
                <p class="mt-1 text-xs text-slate-500">Dummy: coba Dago, Antapani, Buah Batu, Setiabudi, Lembang, Cibiru, Arcamanik, Pasteur, atau Monas (untuk uji di luar Bandung).</p>
            </div>

            <p class="text-sm text-slate-500" x-show="mencari" x-cloak>Mencari lokasi...</p>
            <p class="text-sm text-slate-500" x-show="cariAlamat" x-cloak>Mengambil alamat dari titik peta...</p>
            <p class="rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-900" x-show="kosong" x-cloak>Alamat tidak ditemukan. Perbaiki alamat lalu cari lagi, atau geser pin di peta.</p>

            <ul class="divide-y divide-slate-200 rounded-lg border border-slate-200" x-show="saran.length" x-cloak>
                <template x-for="s in saran" :key="s.nama">
                    <li><button type="button" class="block w-full px-3 py-2 text-left text-sm hover:bg-mist" @click="pilihSaran(s)" x-text="s.nama"></button></li>
                </template>
            </ul>

            <p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700" x-show="luar" x-cloak>Lokasi di luar Bandung Raya. Pilih lokasi lain.</p>

            <div x-ref="peta" class="h-72 rounded-lg border border-slate-200"></div>
            <p class="text-xs text-slate-500">Geser pin atau klik peta untuk menentukan lokasi. Alamat, koordinat, dan MDPL ikut berubah.</p>

            <div class="grid grid-cols-3 gap-3">
                <div><label class="label">Latitude</label><input class="input bg-slate-50" name="lat" :value="lat" readonly></div>
                <div><label class="label">Longitude</label><input class="input bg-slate-50" name="lng" :value="lng" readonly></div>
                <div><label class="label">MDPL (m)</label>
                    <input class="input bg-slate-50" name="mdpl" :value="loadMdpl ? 'Memuat...' : mdpl" readonly></div>
            </div>
            <input type="hidden" name="alamat" :value="alamatDipilih">

            <button class="btn btn-primary w-full" :disabled="!bisaKirim">
                <svg x-show="processing" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 3a9 9 0 1 0 9 9" /></svg>
                <span x-text="processing ? 'Sedang memproses...' : 'Prediksi'"></span>
            </button>
            <p class="text-xs text-slate-500" x-show="!bisaKirim && !processing">Tombol aktif setelah semua kolom wajib terisi dan lokasi dipilih.</p>
        </section>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
<script>
function predictForm() {
    /*
     * DUMMY. Nanti semua pemanggilan ini lewat backend Laravel (API key tidak boleh di browser):
     *   GET /api/geocode?q=...        -> Nominatim /search  (pakai field "display_name" sebagai alamat)
     *   GET /api/reverse?lat=&lng=    -> Nominatim /reverse (pakai field "display_name")
     *   GET /api/elevation?lat=&lng=  -> Elevation API (MDPL)
     * Aturan alamat: kalau API mengirim alamat, SIMPAN alamat dari API (lebih lengkap),
     * bukan teks yang diketik pengguna.
     */
    const DATA = [
        { nama: 'Dago Pakar, Dago, Coblong, Kota Bandung, Jawa Barat, 40135, Indonesia', lat: -6.8590, lng: 107.6190 },
        { nama: 'Dago Asri, Dago, Coblong, Kota Bandung, Jawa Barat, 40135, Indonesia', lat: -6.8780, lng: 107.6120 },
        { nama: 'Antapani, Antapani Kidul, Kota Bandung, Jawa Barat, 40291, Indonesia', lat: -6.9150, lng: 107.6600 },
        { nama: 'Buah Batu, Margasari, Kota Bandung, Jawa Barat, 40287, Indonesia', lat: -6.9560, lng: 107.6360 },
        { nama: 'Setiabudi, Kota Bandung, Jawa Barat, 40154, Indonesia', lat: -6.8600, lng: 107.5950 },
        { nama: 'Lembang, Kabupaten Bandung Barat, Jawa Barat, 40391, Indonesia', lat: -6.8120, lng: 107.6180 },
        { nama: 'Cibiru, Cipadung, Kota Bandung, Jawa Barat, 40614, Indonesia', lat: -6.9230, lng: 107.7200 },
        { nama: 'Arcamanik, Sukamiskin, Kota Bandung, Jawa Barat, 40293, Indonesia', lat: -6.9120, lng: 107.6730 },
        { nama: 'Pasteur, Sukajadi, Kota Bandung, Jawa Barat, 40161, Indonesia', lat: -6.8930, lng: 107.5950 },
        { nama: 'Monas, Gambir, Jakarta Pusat, DKI Jakarta, 10110, Indonesia', lat: -6.1754, lng: 106.8272 },
    ];
    const PUSAT = [-6.9147, 107.6098];
    const dalamBandung = (lat, lng) => lat > -7.25 && lat < -6.70 && lng > 107.40 && lng < 107.90;
    const reverse = (lat, lng) => { // DUMMY: ambil lokasi dummy terdekat, tambah nama jalan karangan
        let best = DATA[0], d = Infinity;
        DATA.forEach(x => { const k = (x.lat - lat) ** 2 + (x.lng - lng) ** 2; if (k < d && dalamBandung(x.lat, x.lng)) { d = k; best = x; } });
        return 'Jl. Contoh No. ' + (Math.abs(Math.round(lat * 1e4)) % 90 + 1) + ', ' + best.nama;
    };

    // Objek Leaflet disimpan di luar data Alpine supaya tidak dibungkus proxy
    let map = null, marker = null, tokenMdpl = 0, tokenAlamat = 0;

    return {
        f: { tanah: '', bangunan: '', kt: '', km: '', garasi: '', deskripsi: '' },
        contoh: 'Rumah 2 lantai dalam cluster one gate system. Ada taman depan dan belakang, carport untuk 2 mobil, dapur terpisah, dan kolam ikan kecil. Dekat sekolah, minimarket, dan akses tol (sekitar 10 menit). Bebas banjir, air PDAM dan sumur bor, kondisi bangunan terawat.',
        alamat: '', alamatDipilih: '', saran: [], mencari: false, cariAlamat: false, kosong: false, luar: false,
        lat: '', lng: '', mdpl: '', loadMdpl: false, processing: false,

        init() {
            map = L.map(this.$refs.peta).setView(PUSAT, 12);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
            // Pin selalu ada dan bisa digeser, walaupun alamat belum diisi
            marker = L.marker(PUSAT, { draggable: true }).addTo(map);
            marker.on('dragend', () => { const p = marker.getLatLng(); this.setTitik(p.lat, p.lng, true); });
            map.on('click', e => { marker.setLatLng(e.latlng); this.setTitik(e.latlng.lat, e.latlng.lng, true); });
        },
        get bisaKirim() {
            const ok = ['tanah', 'bangunan', 'kt', 'km'].every(k => Number(this.f[k]) > 0) && this.f.garasi !== '' && Number(this.f.garasi) >= 0;
            return ok && this.alamatDipilih && this.lat !== '' && this.mdpl !== '' && !this.loadMdpl && !this.cariAlamat && !this.luar && !this.processing;
        },
        reset() { // alamat diketik ulang: kosongkan koordinat dan MDPL (pin tetap di peta)
            tokenMdpl++; tokenAlamat++;
            this.lat = this.lng = this.mdpl = this.alamatDipilih = '';
            this.saran = []; this.kosong = false; this.luar = false; this.loadMdpl = false; this.cariAlamat = false;
        },
        cariLokasi() {
            this.reset(); this.mencari = true;
            setTimeout(() => {
                const kata = this.alamat.toLowerCase().split(/[\s,]+/).filter(k => k.length > 2);
                this.saran = DATA.filter(d => kata.some(k => d.nama.toLowerCase().includes(k))).slice(0, 5);
                this.kosong = this.saran.length === 0; this.mencari = false;
            }, 700);
        },
        pilihSaran(s) {
            this.saran = [];
            this.alamat = this.alamatDipilih = s.nama; // pakai alamat dari API, bukan teks yang diketik
            marker.setLatLng([s.lat, s.lng]);
            map.setView([s.lat, s.lng], dalamBandung(s.lat, s.lng) ? 16 : 10);
            this.setTitik(s.lat, s.lng, false);
        },
        // Dipanggil saat pin digeser / peta diklik (cariAlamat = true) atau saat saran dipilih (false)
        setTitik(lat, lng, cariAlamat) {
            this.lat = lat.toFixed(6); this.lng = lng.toFixed(6);
            this.saran = []; this.kosong = false;
            this.luar = !dalamBandung(lat, lng);
            this.mdpl = ''; this.loadMdpl = false;
            if (this.luar) { if (cariAlamat) this.alamatDipilih = ''; return; }

            const t1 = ++tokenMdpl; this.loadMdpl = true;
            setTimeout(() => { // DUMMY: nilai MDPL karangan, nanti dari Elevation API
                if (t1 !== tokenMdpl) return;
                this.mdpl = Math.round(670 + Math.max(0, lat + 6.95) * 4000);
                this.loadMdpl = false;
            }, 900);

            if (cariAlamat) {
                const t2 = ++tokenAlamat; this.cariAlamat = true; this.alamatDipilih = '';
                setTimeout(() => { // DUMMY: nanti dari Nominatim /reverse
                    if (t2 !== tokenAlamat) return;
                    this.alamat = this.alamatDipilih = reverse(lat, lng);
                    this.cariAlamat = false;
                }, 700);
            }
        },
    };
}
</script>
@endpush
