# ✅ Deployment Checklist - Render + TiDB

Gunakan checklist ini untuk memastikan semua sudah siap sebelum deploy.

---

## 📦 GitHub Repository

- [x] Repository URL: https://github.com/Ranggard/Website-Taksir-Rumah-Bandung
- [x] Branch: `main`
- [x] Code sudah di-push
- [x] README updated dengan deploy instructions
- [x] `.env.example` updated untuk MySQL

**Status:** ✅ Ready

---

## 🗄️ TiDB Cloud Setup

### Checklist:

- [ ] **Akun TiDB Cloud dibuat**
  - URL: https://tidbcloud.com/
  - Sign up dengan GitHub
  - Email verified

- [ ] **Cluster Serverless dibuat**
  - Nama: `prediksi-rumah-bandung`
  - Type: Serverless (Free tier)
  - Region: Singapore (atau terdekat)
  - Status: "Available" ✓

- [ ] **Database dibuat**
  ```sql
  CREATE DATABASE prediksi_rumah_bandung;
  ```

- [ ] **Credentials dikumpulkan**
  - [ ] Host: `gateway01.ap-southeast-1.prod.aws.tidbcloud.com`
  - [ ] Port: `4000`
  - [ ] Database: `prediksi_rumah_bandung`
  - [ ] Username: `root`
  - [ ] Password: _________________ (simpan aman!)

- [ ] **IP Whitelist dikonfigurasi**
  - Add: `0.0.0.0/0` (Allow all)
  - [ ] Configuration saved

**Status:** ⏳ Pending (Jalankan langkah di atas)

---

## 🚀 Render Deployment Setup

### Checklist:

- [ ] **Render account dibuat**
  - URL: https://dashboard.render.com/
  - Sign up dengan GitHub
  - GitHub authorized

- [ ] **Web Service dibuat**
  - [ ] Repository selected: `Website-Taksir-Rumah-Bandung`
  - [ ] Branch: `main`
  - [ ] Service name: `taksir-rumah-bandung`
  - [ ] Environment: `PHP 8.3`
  - [ ] Region: `Singapore` (atau terdekat)
  - [ ] Plan: `Free` ✅

- [ ] **Build Command diset**
  ```bash
  composer install --optimize-autoloader --no-dev && npm install && npm run build
  ```

- [ ] **Start Command diset**
  ```bash
  php artisan migrate --force && php -S 0.0.0.0:$PORT public/index.php
  ```

- [ ] **Service dibuat dan status Live**
  - [ ] URL diberikan (contoh: `https://taksir-rumah-bandung.onrender.com`)
  - [ ] Status: "Live" (hijau) ✅

**Status:** ⏳ Pending (Jalankan langkah di atas)

---

## 🔑 Environment Variables di Render

### Checklist:

- [ ] **Generate APP_KEY lokal**
  ```bash
  php artisan key:generate --show
  ```
  Copy output: `base64:___________________`

- [ ] **Environment Variables diset di Render**
  - [ ] APP_NAME: `Taksir Rumah Bandung`
  - [ ] APP_ENV: `production`
  - [ ] APP_DEBUG: `false`
  - [ ] APP_URL: `https://your-render-url.onrender.com`
  - [ ] APP_KEY: `base64:xxxxx` (dari step di atas)

  - [ ] DB_CONNECTION: `mysql`
  - [ ] DB_HOST: `gateway01.ap-southeast-1.prod.aws.tidbcloud.com`
  - [ ] DB_PORT: `4000`
  - [ ] DB_DATABASE: `prediksi_rumah_bandung`
  - [ ] DB_USERNAME: `root`
  - [ ] DB_PASSWORD: `your_tidb_password`
  - [ ] DB_CHARSET: `utf8mb4`
  - [ ] DB_COLLATION: `utf8mb4_unicode_ci`

  - [ ] SESSION_DRIVER: `database`
  - [ ] QUEUE_CONNECTION: `database`
  - [ ] CACHE_STORE: `database`
  - [ ] LOG_CHANNEL: `stderr`

- [ ] **Variables disimpan**
  - Render otomatis redeploy

**Status:** ⏳ Pending (Jalankan langkah di atas)

---

## 🧪 Post-Deployment Verification

### Checklist:

- [ ] **Cek Render Logs**
  - [ ] Build berhasil (no errors)
  - [ ] Migration ran successfully
  - [ ] Server running on port

- [ ] **Test Aplikasi**
  - [ ] Navigate ke URL Render
  - [ ] Page loads tanpa error
  - [ ] Database connected (check di logs)

- [ ] **Feature Test**
  - [ ] Homepage loads
  - [ ] Can access authentication pages
  - [ ] API responses OK

- [ ] **Database Verification**
  - [ ] Tables created in TiDB
  - [ ] Data persists across requests
  - [ ] Migrations applied

**Status:** ⏳ Pending (Jalankan setelah deployment)

---

## 🔒 Security Checklist

- [ ] **APP_KEY tidak ada di .env di GitHub**
  - Gunakan Render environment variables

- [ ] **Database password tidak di hardcode**
  - Semua credentials di Render env vars

- [ ] **SSL/HTTPS enabled**
  - Render auto-enable (default)

- [ ] **Database backups configured**
  - TiDB auto-backup (daily)

- [ ] **Monitoring enabled**
  - Render dashboard untuk metrics
  - TiDB metrics untuk database

**Status:** ⏳ Pending (Review setelah deployment)

---

## 💾 Backup & Disaster Recovery

- [ ] **Database backup schedule**
  - Manual: Export via mysqldump
  - Automatic: TiDB daily backups

- [ ] **Code repository**
  - GitHub main branch
  - Regular commits

- [ ] **Environment variables backup**
  - Store credentials securely
  - Never commit to GitHub

**Status:** ⏳ Pending (Setup after live)

---

## 📊 Monitoring Setup

- [ ] **Render Metrics Enabled**
  - CPU & Memory usage
  - Response times
  - Error rates

- [ ] **TiDB Metrics Enabled**
  - Database connections
  - Query performance
  - Storage usage

- [ ] **Logs Accessible**
  - Render logs
  - TiDB logs
  - Application logs

**Status:** ⏳ Pending (Auto-enabled on Render & TiDB)

---

## 🔄 Continuous Deployment

- [ ] **GitHub integration working**
  - Push to `main` → Auto-deploy to Render

- [ ] **Automatic redeploy**
  - Test: Make change locally, push, verify redeploy

- [ ] **Build success on commit**
  - All deployments building successfully

**Status:** ⏳ Pending (Setup automatic after first manual deploy)

---

## ⚠️ Common Issues Prevention

| Issue | Prevention | Status |
|-------|-----------|--------|
| APP_KEY not set | Set in Render env vars | ⏳ |
| DB connection fail | Verify credentials, IP whitelist | ⏳ |
| Migration error | Database exists, check logs | ⏳ |
| Permission denied | Check Laravel file permissions | ⏳ |
| 500 Internal Error | Check application logs | ⏳ |

---

## 🎯 Final Steps

1. **Sebelum Deploy:**
   - [ ] Verify semua checklist di atas
   - [ ] Generate APP_KEY lokal
   - [ ] Test lokal dengan MySQL connection

2. **Saat Deploy:**
   - [ ] Create TiDB cluster
   - [ ] Create Render service
   - [ ] Set environment variables
   - [ ] Verify build & deployment

3. **Setelah Deploy:**
   - [ ] Test aplikasi di live URL
   - [ ] Check logs for errors
   - [ ] Monitor untuk 24 jam pertama

---

## 📞 Support & Resources

- **Render Docs**: https://render.com/docs
- **TiDB Docs**: https://docs.pingcap.com/tidbcloud/
- **Laravel Docs**: https://laravel.com/docs
- **This Repository**: https://github.com/Ranggard/Website-Taksir-Rumah-Bandung

---

## 📝 Notes

```
Catatan penting saat deployment:
_________________________________
_________________________________
_________________________________
_________________________________
_________________________________
```

---

**Last Updated:** November 2026
**Status:** Ready for Deployment ✅

**Next Step:** Mulai dari TiDB setup → Render deployment → Set env vars → Done!
