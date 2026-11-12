# 🚀 Quick Deploy Guide (Render + TiDB)

Panduan cepat untuk deploy aplikasi dalam 15 menit.

---

## Step 1️⃣: Setup TiDB (5 menit)

### 1. Buat Akun TiDB
- Kunjungi: https://tidbcloud.com/
- Sign up dengan GitHub
- Verifikasi email

### 2. Buat Cluster Gratis
- Login ke TiDB Cloud
- Klik **"Create Cluster"**
- Pilih **"Serverless"** (gratis!)
- Nama: `prediksi-rumah-bandung`
- Region: Singapore atau terdekat
- Klik **"Create"** (tunggu 5-10 menit)

### 3. Setup Database
Setelah cluster aktif:
- Klik cluster → **"Connect"**
- Copy MySQL connection string
- Buka MySQL client atau Workbench
- Jalankan:
  ```sql
  CREATE DATABASE prediksi_rumah_bandung;
  ```

### 4. Catat Credentials
Dari connection string, catat:
- **Host**: `gateway01.ap-southeast-1.prod.aws.tidbcloud.com` (atau region Anda)
- **Port**: `4000`
- **Username**: `root` (default)
- **Password**: (dari TiDB)
- **Database**: `prediksi_rumah_bandung`

---

## Step 2️⃣: Deploy ke Render (5 menit)

### 1. Login Render
- Kunjungi: https://dashboard.render.com/
- Sign up dengan GitHub

### 2. Create New Web Service
- Klik **"+ New"** → **"Web Service"**
- Pilih **"Build and deploy from Git"**
- **Connect GitHub** (authorize Render)

### 3. Select Repository
- Cari: `Website-Taksir-Rumah-Bandung`
- Klik **"Connect"**

### 4. Configure Deployment

| Setting | Value |
|---------|-------|
| **Name** | `taksir-rumah-bandung` |
| **Environment** | `PHP 8.3` |
| **Region** | `Singapore` (atau terdekat) |
| **Branch** | `main` |
| **Build Command** | `composer install --optimize-autoloader --no-dev && npm install && npm run build` |
| **Start Command** | `php artisan migrate --force && php -S 0.0.0.0:$PORT public/index.php` |
| **Plan** | `Free` ✅ |

### 5. Klik **"Create Web Service"**

---

## Step 3️⃣: Set Environment Variables (3 menit)

Setelah service dibuat, pergi ke **Settings** → **Environment**:

Tambahkan semua variabel ini:

```
APP_NAME=Taksir Rumah Bandung
APP_ENV=production
APP_DEBUG=false
APP_URL=https://taksir-rumah-bandung.onrender.com
APP_KEY=base64:YOUR_KEY_HERE

DB_CONNECTION=mysql
DB_HOST=gateway01.ap-southeast-1.prod.aws.tidbcloud.com
DB_PORT=4000
DB_DATABASE=prediksi_rumah_bandung
DB_USERNAME=root
DB_PASSWORD=YOUR_TIDB_PASSWORD

DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

LOG_CHANNEL=stderr
```

**Ganti:**
- `YOUR_KEY_HERE` → Output dari: `php artisan key:generate --show` (jalankan lokal)
- `YOUR_TIDB_PASSWORD` → Password dari TiDB

### ✅ Klik **"Save"**

---

## Step 4️⃣: Monitor Deployment (2 menit)

- Render akan auto-deploy saat Anda save environment variables
- Buka tab **"Logs"** untuk lihat progress
- Tunggu sampai status berubah menjadi **"Live"** (warna hijau)

### Cek Log:
```
Building on commit xxx
...
npm run build
...
php artisan migrate --force
...
Server running on 0.0.0.0:10000
```

---

## Step 5️⃣: Test Aplikasi ✨

URL Anda akan muncul di atas dashboard, contoh:
```
https://taksir-rumah-bandung.onrender.com
```

Kunjungi URL tersebut di browser → Selesai! 🎉

---

## 📝 Troubleshooting

### ❌ Deployment Failed (Red)
1. Klik service → **"Logs"**
2. Cari error message
3. Common issues:
   - **Missing APP_KEY**: `php artisan key:generate --show` (copy-paste ke env)
   - **Database connection failed**: Check credentials di TiDB
   - **Migration error**: Database belum create di TiDB

### ❌ Application Running but 500 Error
1. Klik **"Logs"** → lihat error
2. Kemungkinan: Database credentials salah atau database belum ada
3. Verify:
   - Database `prediksi_rumah_bandung` sudah dibuat?
   - TiDB cluster masih active?
   - Credentials di env variables benar?

### ❌ TiDB Connection Timeout
1. Login TiDB Cloud
2. Klik cluster → **"Connection"**
3. Pastikan IP whitelist: Allow `0.0.0.0/0` (allow all)

---

## 🔄 Update Aplikasi

Setiap kali push ke GitHub:
```bash
git add .
git commit -m "Update aplikasi"
git push origin main
```

Render otomatis redeploy! Cek status di Render dashboard.

---

## 💡 Free Limits

- **Render**: 750 jam/bulan (24/7 untuk 1 app)
- **TiDB**: 1 juta request/bulan
- **Storage**: Ephemeral (file hilang saat restart)
  - Solusi: Gunakan cloud storage (AWS S3, dll)

---

## 📚 Full Documentation
Baca `HOSTING.md` untuk panduan lengkap dan advanced setup.

---

**Status:** ✅ Ready to Deploy!
