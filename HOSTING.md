# Panduan Hosting di Render + Database TiDB (MySQL)

## ✅ Syarat Gratis
- **Render**: Instance gratis (free tier) tersedia
- **TiDB**: Serverless tier gratis (1 juta request/bulan)

---

## 🔧 TAHAP 1: Persiapan Project (Local)

### 1.1 Update `.env` untuk Production
Pastikan file `.env` sudah di-push ke GitHub (jangan lupa gitignore di Render nanti):

```bash
git add .
git commit -m "Update environment for production"
git push origin main
```

### 1.2 Update `Procfile` untuk Render
Render membutuhkan `Procfile` untuk menjalankan aplikasi. Buat file di root project:

```procfile
web: gunicorn config.wsgi
release: php artisan migrate --force
web: php artisan serve --host=0.0.0.0 --port=$PORT
```

**ATAU** gunakan build command Render (lebih mudah):

---

## 🎯 TAHAP 2: Setup TiDB (Database Gratis)

### 2.1 Buat Akun TiDB Cloud
1. Kunjungi: https://tidbcloud.com/
2. Sign up dengan GitHub atau email
3. Verifikasi email

### 2.2 Buat Cluster TiDB Gratis
1. Setelah login, klik **"Create Cluster"**
2. Pilih **"Serverless"** (ini yang gratis!)
3. Nama cluster: `prediksi-rumah-bandung`
4. Region: Pilih yang terdekat (misal: Singapore)
5. Klik **"Create"** (tunggu 5-10 menit)

### 2.3 Dapatkan Connection String
1. Setelah cluster dibuat, klik cluster tersebut
2. Klik **"Connect"**
3. Pilih **"MySQL"** atau **"General"**
4. Copy connection string, contohnya:
   ```
   mysql://[username]:[password]@[host]:[port]/[database]?sslMode=VERIFY_IDENTITY
   ```
5. **PENTING**: Catat username, password, host, dan port

### 2.4 Setup Database di TiDB
Gunakan MySQL client atau GUI seperti MySQL Workbench:

```sql
-- Connect dengan credentials dari TiDB
-- Buat database
CREATE DATABASE IF NOT EXISTS prediksi_rumah_bandung;
USE prediksi_rumah_bandung;

-- Table akan auto-migrate dari Laravel
```

---

## 🚀 TAHAP 3: Deploy ke Render

### 3.1 Persiapkan `.env` untuk Render
Update file `.env.example` dengan template untuk Render:

```env
APP_NAME="Taksir Rumah Bandung"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.onrender.com

# Database - TiDB
DB_CONNECTION=mysql
DB_HOST=gateway01.ap-southeast-1.prod.aws.tidbcloud.com
DB_PORT=4000
DB_DATABASE=prediksi_rumah_bandung
DB_USERNAME=your_username
DB_PASSWORD=your_password
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

### 3.2 Deploy di Render
1. **Login ke Render**: https://dashboard.render.com/
2. Klik **"+ New +"** → **"Web Service"**
3. Pilih **"Build and deploy from a Git repository"**
4. Connect GitHub account (jika belum):
   - Klik **"Connect account"**
   - Authorize Render
5. Pilih repository: `Website-Taksir-Rumah-Bandung`
6. **Konfigurasi deployment**:
   - **Name**: `taksir-rumah-bandung` (atau sesuai keinginan)
   - **Environment**: `PHP 8.3`
   - **Build Command**: 
     ```bash
     composer install && npm install && npm run build
     ```
   - **Start Command**:
     ```bash
     php artisan migrate --force && php -S 0.0.0.0:$PORT public/index.php
     ```
   - **Plan**: `Free` ✅

### 3.3 Tambahkan Environment Variables di Render
Setelah create, pergi ke **Settings** → **Environment**:

Tambahkan variabel:
```
APP_NAME=Taksir Rumah Bandung
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.onrender.com
APP_KEY=base64:xxxxx (generate dengan: php artisan key:generate --show)

DB_CONNECTION=mysql
DB_HOST=gateway01.ap-southeast-1.prod.aws.tidbcloud.com
DB_PORT=4000
DB_DATABASE=prediksi_rumah_bandung
DB_USERNAME=<dari TiDB>
DB_PASSWORD=<dari TiDB>
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

**⚠️ PENTING**: Jangan hardcode credentials di `.env` yang di-push ke GitHub. Gunakan Render's environment variables!

### 3.4 Konfigurasi Storage (Optional)
Jika perlu upload file:
- Render menggunakan ephemeral storage (file hilang saat restart)
- Solusi: Gunakan cloud storage (AWS S3, Google Cloud Storage)
- Atau ubah `FILESYSTEM_DISK` ke `s3` di `.env`

---

## 🔑 TAHAP 4: Setup Laravel Key Generation

### 4.1 Generate APP_KEY Lokal
Sebelum deploy, generate APP_KEY:

```bash
php artisan key:generate --show
```

Copy output (format: `base64:xxxxx`), gunakan ini di Render environment variables.

### 4.2 Alternative: Render Auto Generate
Jika tidak ada APP_KEY di env variables, Render akan gagal. Pastikan sudah diset!

---

## ✨ TAHAP 5: Verifikasi Deployment

### 5.1 Cek Status di Render
1. Buka Render dashboard
2. Klik service Anda
3. Lihat **Logs** → pastikan tidak ada error
4. URL Anda akan muncul di atas, contoh: `https://taksir-rumah-bandung.onrender.com`

### 5.2 Test Aplikasi
```bash
curl https://your-app-name.onrender.com
```

### 5.3 Jalankan Migration (jika belum auto-run)
Di Render console (tidak ada terminal interaktif), migration otomatis dari build command.

Jika manual diperlukan, gunakan Render's **"Shell"** (tersedia untuk Pro plan) atau:
```bash
# Update Start Command di Render untuk menjalankan migration
php artisan migrate --force && php -S 0.0.0.0:$PORT public/index.php
```

---

## 🐛 Troubleshooting

### ❌ Error: "Class 'PDO' not found"
**Solusi**: Pastikan `php` build command include extension mysql:
```bash
# Di Render Build Command
composer install --optimize-autoloader --no-dev
```

### ❌ Database Connection Failed
**Solusi**: 
1. Verify credentials di TiDB dashboard
2. Periksa IP whitelist TiDB (allow all: `0.0.0.0/0`)
3. Test connection manual dari MySQL client

### ❌ APP_KEY Error
**Solusi**: Generate dan set di Render environment:
```bash
php artisan key:generate --show
```
Copy dan paste di Render env vars.

### ❌ Session/Cache Error
**Solusi**: Pastikan `SESSION_DRIVER=database` dan migration sudah jalan:
```bash
php artisan migrate --force
```

---

## 📋 Checklist Deploy

- [ ] Code pushed ke GitHub (`main` branch)
- [ ] TiDB cluster dibuat dan running
- [ ] Database credentials siap
- [ ] `.env.example` sudah update dengan DB_CONNECTION=mysql
- [ ] APP_KEY sudah generate
- [ ] Render project dibuat
- [ ] Environment variables sudah diset di Render
- [ ] Build command: `composer install && npm install && npm run build`
- [ ] Start command: `php artisan migrate --force && php -S 0.0.0.0:$PORT public/index.php`
- [ ] Deployment berhasil (lihat di Logs)
- [ ] Test aplikasi di URL yang diberikan

---

## 📌 Link Penting

- **Render Dashboard**: https://dashboard.render.com/
- **TiDB Cloud**: https://tidbcloud.com/
- **Laravel Docs**: https://laravel.com/docs
- **MySQL TiDB Connection**: https://docs.pingcap.com/tidbcloud/

---

## 💡 Tips Gratis

1. **Render Free Tier**: 750 jam/bulan (cukup untuk 1 app 24/7)
2. **TiDB Serverless Free**: 1 juta request/bulan
3. **Upgrade kapan saja** saat traffic meningkat
4. **Monitor costs** di Render & TiDB dashboard

---

**Selesai! Aplikasi Anda sekarang live di Render dengan database TiDB! 🎉**
