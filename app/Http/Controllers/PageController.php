<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Controller sementara untuk tahap FRONT END.
 * Semua data masih dummy. Di tahap backend, bagian bertanda "DUMMY"
 * diganti dengan model, database, FastAPI, dan Gemini.
 */
class PageController extends Controller
{
    private const DEMO_EMAIL = 'demo@gmail.com';
    private const DEMO_PASS = 'password123';

    // ================= Beranda =================
    public function home()
    {
        return view('home');
    }

    // ================= Auth (DUMMY) =================
    public function loginForm()
    {
        return view('auth.login');
    }

    public function loginStore(Request $r)
    {
        if (strtolower((string) $r->input('email')) === self::DEMO_EMAIL && $r->input('password') === self::DEMO_PASS) {
            $r->session()->regenerate();
            session(['user' => ['name' => 'Demo Pengguna', 'email' => self::DEMO_EMAIL]]);
            return redirect(session()->pull('intended', route('home')));
        }
        // Pesan sengaja tidak menyebut mana yang salah
        return back()->withInput($r->only('email'))->withErrors(['login' => 'Email atau password salah.']);
    }

    public function registerForm()
    {
        return view('auth.register');
    }

    public function registerStore(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', 'regex:/@gmail\.com$/i'],
            'password' => 'required|min:8|confirmed',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.regex' => 'Email harus berakhiran @gmail.com.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        if (strtolower($data['email']) === self::DEMO_EMAIL) { // DUMMY: anggap sudah terdaftar
            return back()->withInput($r->only('name', 'email'))->withErrors(['email' => 'Email sudah terdaftar.']);
        }

        $r->session()->regenerate();
        session(['user' => ['name' => $data['name'], 'email' => $data['email']]]);
        return redirect(session()->pull('intended', route('home')))->with('success', 'Pendaftaran berhasil.');
    }

    public function forgotForm()
    {
        return view('auth.forgot');
    }

    public function forgotDone(Request $r)
    {
        $r->validate(['password' => 'required|min:8|confirmed']);
        return redirect()->route('login')->with('success', 'Password berhasil diubah.');
    }

    public function logout(Request $r)
    {
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('home');
    }

    // ================= Prediksi =================
    public function predictForm()
    {
        return view('predict');
    }

    public function predictStore(Request $r)
    {
        $d = $r->validate([
            'tanah' => 'required|integer|min:1|max:100000',
            'bangunan' => 'required|integer|min:1|max:100000',
            'kt' => 'required|integer|min:1|max:50',
            'km' => 'required|integer|min:1|max:50',
            'garasi' => 'required|integer|min:0|max:20',
            'sertifikat' => 'nullable|in:SHM,HGB,Girik,Lainnya',
            'listrik' => 'nullable|in:900,1300,2200,3500,4400,5500',
            'furnishing' => 'nullable|in:Tanpa furnitur,Semi furnished,Fully furnished',
            'deskripsi' => 'nullable|string|max:1000',
            'alamat' => 'required|string|max:500',
            'lat' => 'required|numeric|between:-7.25,-6.70',
            'lng' => 'required|numeric|between:107.40,107.90',
            'mdpl' => 'required|numeric|between:300,2500',
        ], [
            'lat.between' => 'Lokasi di luar Bandung Raya.',
            'lng.between' => 'Lokasi di luar Bandung Raya.',
        ]);

        // DUMMY: nanti panggil FastAPI /predict + Gemini, lalu simpan ke MySQL
        $item = $this->bangun(0, $d, now()->format('d/m/Y H:i'));
        session(['last' => $item]);
        return redirect()->route('result', 0);
    }

    public function result(Request $r, $id)
    {
        $id = (int) $id;
        $item = $id === 0 ? session('last') : collect($this->riwayat())->firstWhere('id', $id);
        abort_unless($item, 404); // nanti: hanya riwayat milik user yang login (404/403)
        if ($r->query('insight') === 'gagal') {
            $item['insight'] = null; // untuk menguji tampilan "Insight belum tersedia"
        }
        return view('result', compact('item'));
    }

    // ================= Riwayat =================
    public function history(Request $r)
    {
        $semua = $r->boolean('kosong') ? [] : $this->riwayat(); // ?kosong=1 untuk uji tampilan kosong
        $total = count($semua);

        // Parameter dari form pencarian/filter (GET)
        $q = mb_strtolower(trim((string) $r->query('q')));
        $harga = (string) $r->query('harga', 'all');      // all | 0-1 | 1-2 | 2-4 | 4-99 (miliar rupiah)
        $kt = (int) $r->query('kt', 0);                    // minimal kamar tidur
        $insight = (string) $r->query('insight', 'all');   // all | ada | belum
        $dari = (string) $r->query('dari');                // Y-m-d
        $sampai = (string) $r->query('sampai');            // Y-m-d
        $sort = (string) $r->query('sort', 'terbaru');
        $per = in_array((int) $r->query('per'), [5, 10, 20], true) ? (int) $r->query('per') : 5;

        // DUMMY: nanti jadi query Eloquent ->where('user_id', auth()->id()) + where/orderBy
        $hasil = collect($semua)->filter(function ($it) use ($q, $harga, $kt, $insight, $dari, $sampai) {
            if ($q !== '') {
                $teks = mb_strtolower(implode(' ', [$it['alamat'], $it['deskripsi'] ?? '', $it['sertifikat'] ?? '', $it['furnishing'] ?? '']));
                if (!str_contains($teks, $q)) return false;
            }
            if (preg_match('/^(\d+)-(\d+)$/', $harga, $m)) {
                if ($it['harga'] < $m[1] * 1e9 || $it['harga'] >= $m[2] * 1e9) return false;
            }
            if ($kt > 0 && $it['kt'] < $kt) return false;
            if ($insight === 'ada' && !$it['insight']) return false;
            if ($insight === 'belum' && $it['insight']) return false;
            if ($dari !== '' && $it['ts'] < strtotime($dari . ' 00:00:00')) return false;
            if ($sampai !== '' && $it['ts'] > strtotime($sampai . ' 23:59:59')) return false;
            return true;
        });

        $hasil = match ($sort) {
            'terlama' => $hasil->sortBy('ts'),
            'harga_tinggi' => $hasil->sortByDesc('harga'),
            'harga_rendah' => $hasil->sortBy('harga'),
            'tanah_besar' => $hasil->sortByDesc('tanah'),
            'alamat' => $hasil->sortBy('alamat'),
            default => $hasil->sortByDesc('ts'),
        };
        $hasil = $hasil->values();

        $page = max(1, (int) $r->query('page', 1));
        $items = (new LengthAwarePaginator(
            $hasil->slice(($page - 1) * $per, $per)->values()->all(), $hasil->count(), $per, $page, ['path' => $r->url()]
        ))->withQueryString();

        $aktif = collect($r->only(['q', 'harga', 'kt', 'insight', 'dari', 'sampai']))
            ->filter(fn($v) => $v !== null && $v !== '' && $v !== 'all' && $v !== '0')->isNotEmpty();

        return view('history', compact('items', 'total', 'aktif'));
    }

    public function historyDestroy($id)
    {
        return redirect()->route('history')->with('success', 'Riwayat dihapus (dummy, belum menghapus data).');
    }

    // ================= Peta & Profil =================
    public function map()
    {
        return view('map');
    }

    public function profile()
    {
        return view('profile');
    }

    public function profileUpdate(Request $r)
    {
        // Satu form: data akun + (opsional) ganti password
        $d = $r->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', 'regex:/@gmail\.com$/i'],
            'password_lama' => 'nullable|required_with:password_baru',
            'password_baru' => 'nullable|min:8|confirmed|required_with:password_lama',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.regex' => 'Email harus berakhiran @gmail.com.',
            'password_lama.required_with' => 'Password lama wajib diisi untuk mengubah password.',
            'password_baru.required_with' => 'Password baru wajib diisi.',
            'password_baru.min' => 'Password baru minimal 8 karakter.',
            'password_baru.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        if (filled($d['password_lama'] ?? null) && $d['password_lama'] !== self::DEMO_PASS) { // DUMMY
            return back()->withInput($r->only('name', 'email'))->withErrors(['password_lama' => 'Password lama salah.']);
        }

        session(['user' => ['name' => $d['name'], 'email' => $d['email']]]);
        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    // ================= Data DUMMY =================
    private function riwayat(): array
    {
        $lokasi = [
            ['Dago Pakar, Coblong, Bandung', -6.8590, 107.6190, 1020],
            ['Antapani, Bandung', -6.9150, 107.6600, 690],
            ['Buah Batu, Bandung', -6.9560, 107.6360, 670],
            ['Setiabudi, Bandung', -6.8600, 107.5950, 1080],
            ['Lembang, Bandung Barat', -6.8120, 107.6180, 1250],
            ['Cibiru, Bandung', -6.9230, 107.7200, 740],
            ['Arcamanik, Bandung', -6.9120, 107.6730, 710],
            ['Pasteur, Sukajadi, Bandung', -6.8930, 107.5950, 730],
        ];
        $sert = ['SHM', 'HGB', 'SHM', 'Girik'];
        $lis = ['1300', '2200', '900', '3500'];
        $furn = ['Semi furnished', 'Tanpa furnitur', 'Fully furnished', 'Semi furnished'];
        $desk = [
            'Taman depan dan belakang, carport 2 mobil, dekat sekolah dan minimarket.',
            'Rumah 2 lantai dalam cluster one gate system, bebas banjir.',
            null,
            'Dapur terpisah, kolam ikan kecil, udara sejuk, akses angkot mudah.',
        ];

        $out = [];
        for ($i = 1; $i <= 12; $i++) {
            $l = $lokasi[($i - 1) % 8];
            $kosong = $i % 5 === 0; // contoh data opsional yang tidak diisi
            $dt = now()->subDays($i * 3);
            $out[] = $this->bangun($i, [
                'alamat' => $l[0], 'lat' => $l[1], 'lng' => $l[2], 'mdpl' => $l[3],
                'tanah' => 60 + (($i * 37) % 220), 'bangunan' => 40 + (($i * 23) % 140),
                'kt' => 2 + $i % 4, 'km' => 1 + $i % 3, 'garasi' => $i % 3,
                'sertifikat' => $kosong ? null : $sert[$i % 4],
                'listrik' => $kosong ? null : $lis[$i % 4],
                'furnishing' => $kosong ? null : $furn[$i % 4],
                'deskripsi' => $kosong ? null : $desk[$i % 4],
            ], $dt->format('d/m/Y H:i'), $dt->timestamp);
        }
        return $out;
    }

    private function bangun(int $id, array $in, string $tanggal, ?int $ts = null): array
    {
        $in += ['sertifikat' => null, 'listrik' => null, 'furnishing' => null, 'deskripsi' => null];
        $harga = $this->hitung($in);
        return $in + [
            'id' => $id,
            'tanggal' => $tanggal,
            'ts' => $ts ?? time(),
            'harga' => $harga,
            'harga_fmt' => $this->rp($harga),
            'harga_ringkas' => $this->ringkas($harga),
            'insight' => $id === 7 ? null : $this->insight($in, $harga), // id 7: contoh Gemini gagal
        ];
    }

    private function hitung(array $in): int
    {
        $h = $in['tanah'] * 5_500_000 + $in['bangunan'] * 4_000_000 + $in['kt'] * 35_000_000
            + $in['km'] * 25_000_000 + $in['garasi'] * 50_000_000;
        $h *= 1 + max(0, $in['mdpl'] - 700) / 1800;
        return (int) (round($h / 5_000_000) * 5_000_000);
    }

    private function insight(array $in, int $harga): array
    {
        $area = trim(explode(',', $in['alamat'])[0]);
        return [
            'kelebihan' => [
                "Lokasi di {$area} berada di ketinggian {$in['mdpl']} mdpl, dengan udara relatif sejuk.",
                "Luas tanah {$in['tanah']} m² memberi ruang untuk renovasi atau penambahan bangunan.",
            ],
            'kekurangan' => [
                'Akses jalan dan kepadatan lalu lintas perlu dicek langsung saat jam sibuk.',
                'Harga bisa berbeda jauh antar gang di area yang sama.',
            ],
            'saran' => [
                'Bandingkan dengan 3 properti serupa di sekitar lokasi sebelum menawar.',
                'Pastikan legalitas sertifikat dan IMB sudah sesuai sebelum transaksi.',
            ],
            'proyeksi' => "Jika tren kawasan stabil, nilai properti ini bisa naik sekitar 5–8% per tahun. "
                . "Dalam 5 tahun, estimasi kasarnya berada di kisaran " . $this->ringkas((int) ($harga * 1.3)) . " (dummy).",
        ];
    }

    private function rp(int $v): string
    {
        return 'Rp ' . number_format($v, 0, ',', '.');
    }

    private function ringkas(int $v): string
    {
        return $v >= 1_000_000_000
            ? 'Rp ' . number_format($v / 1_000_000_000, 2, ',', '.') . ' M'
            : 'Rp ' . number_format($v / 1_000_000, 0, ',', '.') . ' jt';
    }
}
