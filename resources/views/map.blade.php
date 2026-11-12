@extends('layouts.app')
@section('title', 'Peta Sebaran')

@push('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">
<style>
    .shp-kotak { width: 60px; height: 60px; border-radius: 10px; border: 2px solid #fff; color: #fff; display: flex; flex-direction: column; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,.35); font: 600 11px/1.15 'Instrument Sans', sans-serif; text-shadow: 0 1px 1px rgba(0,0,0,.35); }
    .shp-belah { width: 42px; height: 42px; margin: 8px; transform: rotate(45deg); border: 2px solid #fff; border-radius: 6px; display: grid; place-items: center; box-shadow: 0 2px 6px rgba(0,0,0,.35); }
    .shp-belah > div { transform: rotate(-45deg); display: flex; flex-direction: column; align-items: center; color: #fff; font: 600 10px/1.15 'Instrument Sans', sans-serif; text-shadow: 0 1px 1px rgba(0,0,0,.35); }
    .shp-kotak b, .shp-belah b { font-size: 14px; }
    .lg-kotak { width: 16px; height: 16px; border-radius: 4px; display: inline-block; }
    .lg-belah { width: 12px; height: 12px; transform: rotate(45deg); border-radius: 2px; display: inline-block; margin: 2px; }
</style>
@endpush

@section('content')
<div class="mx-auto max-w-6xl px-5 py-8" x-data="peta()" x-init="init()">
    <h1 class="font-display text-3xl font-bold text-ink">Peta sebaran harga properti</h1>
    <p class="mt-1 text-sm text-slate-600">Menampilkan data properti dari dataset pelatihan model, bukan hasil prediksi Anda.</p>

    {{-- Penjelasan fungsi dan cara pakai --}}
    <details open class="card mt-5 !p-4">
        <summary class="cursor-pointer font-display text-lg font-bold text-ink">Untuk apa peta ini dan cara memakainya</summary>
        <div class="mt-3 grid gap-6 text-sm md:grid-cols-2">
            <div>
                <h3 class="font-semibold text-pine">Fungsi</h3>
                <p class="mt-1">Peta ini membantu Anda membandingkan harga di suatu area dengan data nyata yang dipakai melatih model. Anda bisa melihat area mana yang harganya tinggi atau rendah, dan apakah estimasi Anda masuk akal.</p>
                <h3 class="mt-4 font-semibold text-pine">Cara memakai</h3>
                <ol class="mt-1 list-decimal space-y-1 pl-4">
                    <li>Cari nama jalan atau daerah di kolom pencarian, atau geser dan zoom peta.</li>
                    <li>Klik bentuk kotak atau belah ketupat untuk memperbesar ke area itu.</li>
                    <li>Perbesar sampai muncul ikon rumah, lalu klik untuk melihat spesifikasi dan harganya.</li>
                    <li>Pakai filter harga dan ketinggian untuk menyaring, atau aktifkan heatmap untuk melihat area yang mahal.</li>
                </ol>
            </div>
            <div>
                <h3 class="font-semibold text-pine">Tiga tingkat tampilan</h3>
                <p class="mt-1">Bentuk di peta berganti sesuai zoom. Angka besar di dalam bentuk adalah jumlah properti, angka kecil adalah rata-rata harga.</p>
                <ul class="mt-2 space-y-2">
                    <li class="flex items-center gap-3"><span class="lg-kotak bg-pine"></span><span><b>Kotak</b> = kecamatan (zoom jauh)</span></li>
                    <li class="flex items-center gap-3"><span class="lg-belah bg-pine"></span><span><b>Belah ketupat</b> = kelurahan (zoom sedang)</span></li>
                    <li class="flex items-center gap-3">
                        <svg viewBox="0 0 24 24" width="18" height="18"><path d="M12 2 2 11h3v10h5v-6h4v6h5V11h3z" fill="#1e5a4b"/></svg>
                        <span><b>Ikon rumah</b> = satu properti (zoom dekat)</span></li>
                </ul>
                <p class="mt-3 text-xs text-slate-500">Warna menunjukkan harga: <span class="font-semibold" style="color:#2f7a5a">hijau</span> di bawah Rp 1,5 M, <span class="font-semibold" style="color:#b9770e">oranye</span> Rp 1,5–3 M, <span class="font-semibold" style="color:#b4442f">merah</span> di atas Rp 3 M. Untuk kotak dan belah ketupat, warna dihitung dari harga rata-rata.</p>
            </div>
        </div>
    </details>

    {{-- Pencarian alamat --}}
    <div class="card relative mt-4 !p-4">
        <label class="label" for="cari">Cari alamat atau daerah</label>
        <div class="flex gap-2">
            <input class="input" id="cari" x-model="kata" @keydown.enter.prevent="cari()" placeholder="Contoh: Dago, Antapani, Coblong">
            <button type="button" class="btn btn-primary shrink-0" @click="cari()" :disabled="kata.trim().length < 2">Cari</button>
        </div>
        <p class="mt-1 text-sm text-slate-500" x-show="mencari" x-cloak>Mencari...</p>
        <p class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-900" x-show="tidakAda" x-cloak>Alamat tidak ditemukan. Coba nama kelurahan atau kecamatan lain.</p>
        <ul class="mt-2 divide-y divide-slate-200 rounded-lg border border-slate-200" x-show="hasil.length" x-cloak>
            <template x-for="h in hasil" :key="h.nama">
                <li><button type="button" class="block w-full px-3 py-2 text-left text-sm hover:bg-mist" @click="pilih(h)" x-text="h.nama"></button></li>
            </template>
        </ul>
    </div>

    {{-- Filter --}}
    <div class="card mt-4 flex flex-wrap items-end gap-4 !p-4">
        <div><label class="label">Rentang harga</label>
            <select class="input" x-model="harga" @change="render()">
                <option value="all">Semua harga</option><option value="0-1">Di bawah Rp 1 M</option>
                <option value="1-2">Rp 1 M – 2 M</option><option value="2-4">Rp 2 M – 4 M</option><option value="4-99">Di atas Rp 4 M</option>
            </select></div>
        <div><label class="label">Ketinggian</label>
            <select class="input" x-model="tinggi" @change="render()">
                <option value="all">Semua</option><option value="rendah">Rendah (&lt; 800 m)</option>
                <option value="sedang">Sedang (800–1.000 m)</option><option value="tinggi">Tinggi (&gt; 1.000 m)</option>
            </select></div>
        <label class="flex items-center gap-2 pb-2 text-sm"><input type="checkbox" x-model="heat" @change="render()" class="h-4 w-4 accent-pine"> Layer heatmap</label>
        <div class="ml-auto pb-2 text-right text-sm text-slate-600">
            <p>Tampilan: <b class="text-ink" x-text="namaLevel"></b></p>
            <p><b x-text="jumlah"></b> properti di area ini</p>
        </div>
    </div>

    <div class="relative mt-4">
        <div x-ref="peta" class="h-[60vh] rounded-xl border border-slate-200"></div>
        <div x-show="jumlah === 0" x-cloak class="absolute left-1/2 top-4 z-[500] -translate-x-1/2 rounded-lg bg-white px-4 py-2 text-sm shadow">Tidak ada properti yang sesuai filter.</div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.heat/0.2.0/leaflet-heat.js"></script>
<script>
function peta() {
    /*
     * DUMMY: 450 titik acak dengan struktur kecamatan > kelurahan > rumah.
     * Nanti: fetch('/api/properti?level=...&bounds=...') ke backend. Dataset perlu kolom kecamatan dan kelurahan
     * (atau diturunkan dari koordinat), lalu backend mengirim data yang sudah diringkas per tingkat.
     * Pencarian alamat nanti lewat backend ke Nominatim.
     */
    const WIL = [ // [kecamatan, kelurahan, lat, lng, mdpl]
        ['Coblong', 'Dago', -6.885, 107.613, 1000], ['Coblong', 'Lebak Siliwangi', -6.889, 107.608, 960],
        ['Cidadap', 'Ciumbuleuit', -6.873, 107.605, 900], ['Cidadap', 'Hegarmanah', -6.878, 107.596, 890],
        ['Sukajadi', 'Pasteur', -6.893, 107.595, 730], ['Sukajadi', 'Sukabungah', -6.887, 107.587, 800],
        ['Antapani', 'Antapani Kidul', -6.915, 107.660, 690], ['Antapani', 'Antapani Tengah', -6.910, 107.665, 700],
        ['Buahbatu', 'Margasari', -6.956, 107.636, 670], ['Buahbatu', 'Cijagra', -6.948, 107.633, 675],
        ['Arcamanik', 'Sukamiskin', -6.910, 107.670, 710], ['Arcamanik', 'Cisaranten Kulon', -6.920, 107.678, 705],
        ['Cibiru', 'Cipadung', -6.918, 107.723, 740], ['Cibiru', 'Pasirbiru', -6.926, 107.718, 735],
        ['Lembang', 'Lembang', -6.812, 107.618, 1250], ['Lembang', 'Cikole', -6.796, 107.633, 1300],
    ];
    const rng = s => () => { s |= 0; s = s + 0x6D2B79F5 | 0; let t = Math.imul(s ^ s >>> 15, 1 | s); t = t + Math.imul(t ^ t >>> 7, 61 | t) ^ t; return ((t ^ t >>> 14) >>> 0) / 4294967296; };
    const r = rng(42), titik = [];
    for (let i = 0; i < 450; i++) {
        const [kec, kel, la, lo, m0] = WIL[Math.floor(r() * WIL.length)];
        const tanah = 50 + Math.round(r() * 350), bangunan = 36 + Math.round(r() * 260), mdpl = Math.round(m0 + (r() - 0.5) * 60);
        const harga = Math.round((tanah * 5.5e6 + bangunan * 4e6) * (1 + (mdpl - 700) / 1500) * (0.8 + r() * 0.5) / 5e6) * 5e6;
        titik.push({ kec, kel, lat: la + (r() - 0.5) * 0.008, lng: lo + (r() - 0.5) * 0.008, tanah, bangunan, kt: 2 + Math.floor(r() * 4), km: 1 + Math.floor(r() * 3), mdpl, harga });
    }
    // Bahan pencarian dummy: kelurahan (zoom 16) dan kecamatan (zoom 14)
    const LOKASI = WIL.map(w => ({ nama: `${w[1]}, ${w[0]}, Kota Bandung, Jawa Barat`, lat: w[2], lng: w[3], zoom: 16 }));
    [...new Set(WIL.map(w => w[0]))].forEach(k => {
        const a = WIL.filter(w => w[0] === k);
        LOKASI.push({ nama: `Kecamatan ${k}, Kota Bandung, Jawa Barat`, lat: a.reduce((s, w) => s + w[2], 0) / a.length, lng: a.reduce((s, w) => s + w[3], 0) / a.length, zoom: 14 });
    });

    const fmt = v => v >= 1e9 ? 'Rp ' + (v / 1e9).toFixed(2).replace('.', ',') + ' M' : 'Rp ' + Math.round(v / 1e6) + ' jt';
    const pendek = v => v >= 1e9 ? (v / 1e9).toFixed(1).replace('.', ',') + ' M' : Math.round(v / 1e6) + ' jt';
    const warna = v => v < 1.5e9 ? '#2f7a5a' : v < 3e9 ? '#b9770e' : '#b4442f';

    const ikonKotak = g => L.divIcon({ className: '', iconSize: [60, 60], html: `<div class="shp-kotak" style="background:${warna(g.avg)}"><b>${g.n}</b><span>${pendek(g.avg)}</span></div>` });
    const ikonBelah = g => L.divIcon({ className: '', iconSize: [58, 58], html: `<div class="shp-belah" style="background:${warna(g.avg)}"><div><b>${g.n}</b><span>${pendek(g.avg)}</span></div></div>` });
    const ikonRumah = p => L.divIcon({ className: '', iconSize: [28, 28], iconAnchor: [14, 24],
        html: `<svg viewBox="0 0 24 24" width="28" height="28"><path d="M12 2 2 11h3v10h5v-6h4v6h5V11h3z" fill="${warna(p.harga)}" stroke="#fff" stroke-width="1.6" stroke-linejoin="round"/></svg>` });

    function agregat(daftar, kunci) {
        const m = new Map();
        daftar.forEach(p => {
            const k = p[kunci];
            if (!m.has(k)) m.set(k, { nama: k, n: 0, sum: 0, la: 0, lo: 0, b: L.latLngBounds([[p.lat, p.lng]]) });
            const g = m.get(k); g.n++; g.sum += p.harga; g.la += p.lat; g.lo += p.lng; g.b.extend([p.lat, p.lng]);
        });
        return [...m.values()].map(g => ({ nama: g.nama, n: g.n, avg: g.sum / g.n, lat: g.la / g.n, lng: g.lo / g.n, b: g.b }));
    }

    // Objek Leaflet disimpan di luar data Alpine supaya tidak dibungkus proxy
    let map = null, lapisan = null, heatLayer = null, pin = null;

    return {
        harga: 'all', tinggi: 'all', heat: false, jumlah: 0, level: 'kecamatan',
        kata: '', hasil: [], mencari: false, tidakAda: false,
        get namaLevel() { return { kecamatan: 'Kecamatan (kotak)', kelurahan: 'Kelurahan (belah ketupat)', rumah: 'Rumah (ikon rumah)' }[this.level]; },

        init() {
            map = L.map(this.$refs.peta).setView([-6.9, 107.62], 12);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
            lapisan = L.layerGroup().addTo(map);
            map.on('moveend', () => this.render()); // hanya titik di area yang terlihat
            this.render();
        },
        lolos(p) {
            if (this.harga !== 'all') { const [a, b] = this.harga.split('-').map(Number); if (p.harga < a * 1e9 || p.harga >= b * 1e9) return false; }
            if (this.tinggi === 'rendah' && p.mdpl >= 800) return false;
            if (this.tinggi === 'sedang' && (p.mdpl < 800 || p.mdpl > 1000)) return false;
            if (this.tinggi === 'tinggi' && p.mdpl <= 1000) return false;
            return true;
        },
        render() {
            const z = map.getZoom();
            this.level = z <= 12 ? 'kecamatan' : z <= 14 ? 'kelurahan' : 'rumah';
            const b = map.getBounds();
            const tampil = titik.filter(p => b.contains([p.lat, p.lng]) && this.lolos(p));
            this.jumlah = tampil.length;
            lapisan.clearLayers();

            if (this.level === 'rumah') {
                tampil.forEach(p => L.marker([p.lat, p.lng], { icon: ikonRumah(p) }).addTo(lapisan)
                    .bindPopup(`<b>${p.kel}, ${p.kec}</b><br>LT ${p.tanah} m² · LB ${p.bangunan} m²<br>${p.kt} KT · ${p.km} KM<br>MDPL ${p.mdpl} m<br><b>${fmt(p.harga)}</b>`));
            } else {
                const kec = this.level === 'kecamatan';
                agregat(tampil, kec ? 'kec' : 'kel').forEach(g => {
                    L.marker([g.lat, g.lng], { icon: kec ? ikonKotak(g) : ikonBelah(g) }).addTo(lapisan)
                        .bindTooltip(`${kec ? 'Kecamatan ' : 'Kelurahan '}${g.nama}: ${g.n} properti, rata-rata ${fmt(g.avg)}. Klik untuk memperbesar.`)
                        .on('click', () => map.flyToBounds(g.b.pad(0.3), { maxZoom: kec ? 14 : 16 }));
                });
            }

            if (heatLayer) { map.removeLayer(heatLayer); heatLayer = null; }
            if (this.heat && tampil.length) {
                heatLayer = L.heatLayer(tampil.map(p => [p.lat, p.lng, Math.min(1, p.harga / 6e9)]), { radius: 22, blur: 18 }).addTo(map);
            }
        },
        cari() {
            this.hasil = []; this.tidakAda = false; this.mencari = true;
            setTimeout(() => { // DUMMY: nanti dari backend (Nominatim)
                const kata = this.kata.toLowerCase().split(/[\s,]+/).filter(k => k.length > 1);
                this.hasil = LOKASI.filter(l => kata.every(k => l.nama.toLowerCase().includes(k))).slice(0, 5);
                this.tidakAda = this.hasil.length === 0; this.mencari = false;
            }, 600);
        },
        pilih(h) {
            this.hasil = []; this.kata = h.nama;
            if (pin) map.removeLayer(pin);
            pin = L.marker([h.lat, h.lng]).addTo(map).bindPopup(h.nama).openPopup();
            map.flyTo([h.lat, h.lng], h.zoom);
        },
    };
}
</script>
@endpush
