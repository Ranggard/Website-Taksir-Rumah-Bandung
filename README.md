# 🏠 Website Taksir Rumah Bandung

Aplikasi web untuk prediksi harga rumah di Bandung menggunakan machine learning dan data analytics.

## 📋 Project Info

| Detail | Info |
|--------|------|
| **Framework** | Laravel 13 |
| **PHP Version** | 8.3+ |
| **Database** | MySQL/TiDB |
| **Frontend** | Blade + Tailwind CSS |
| **Status** | Ready for Production |

## ✨ Features

- 🔐 User Authentication & Authorization
- 📊 House Price Prediction (ML Integration)
- 🗺️ Interactive Map View
- 📈 Prediction History
- 👤 User Profile Management
- 🎯 Real-time Price Estimation

## 🚀 Quick Start (Development)

### Prerequisites
- PHP 8.3+
- Composer
- Node.js & npm
- MySQL 5.7+

### Setup

```bash
# Clone repository
git clone https://github.com/Ranggard/Website-Taksir-Rumah-Bandung.git
cd Website-Taksir-Rumah-Bandung

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --seed

# Build assets
npm run build

# Start development server
php artisan serve
```

Access: `http://localhost:8000`

## 🌐 Production Deployment

### Deploy to Railway (Gratis, tanpa kartu kredit)

**Langkah:**
1. Kunjungi https://railway.app/
2. Click **"Start Project"**
3. Login dengan GitHub
4. Select repository: `Website-Taksir-Rumah-Bandung`
5. Railway auto-detect Laravel dan deploy
6. MySQL database auto-created
7. Live URL diberikan otomatis

**Keuntungan:**
- Gratis ($5/bulan credit)
- MySQL included
- Deploy otomatis dari GitHub
- Uptime baik untuk production
- Tidak perlu kartu kredit

**Live URL** akan terlihat seperti:
```
https://website-taksir-rumah-bandung-production.up.railway.app
```

## 📁 Project Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/    # Request handlers
│   │   └── Middleware/     # Auth & request middleware
│   ├── Models/             # Database models
│   └── Providers/          # Service providers
├── database/
│   ├── migrations/         # Database schema
│   └── seeders/            # Database seeding
├── resources/
│   ├── views/              # Blade templates
│   ├── css/                # Tailwind CSS
│   └── js/                 # Frontend JS
├── routes/
│   └── web.php             # Web routes
└── public/
    └── index.php           # Entry point
```

## 🛠️ Technology Stack

- **Backend**: Laravel 13 (PHP)
- **Frontend**: Blade Templating + Tailwind CSS
- **Database**: MySQL 8.0+ / TiDB (MySQL compatible)
- **Build**: Vite (asset bundling)
- **Package Manager**: Composer (PHP) + npm (JS)

## 🔒 Environment Variables

Key variables needed:

```env
APP_NAME=Taksir Rumah Bandung
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database (TiDB or MySQL)
DB_CONNECTION=mysql
DB_HOST=your-database-host
DB_PORT=3306
DB_DATABASE=prediksi_rumah_bandung
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database
```

See `.env.example` for all available variables.

## 📚 Documentation

- **[Laravel Docs](https://laravel.com/docs)** - Framework documentation
- **[Railway Docs](https://docs.railway.app/)** - Deployment documentation

## 🐛 Troubleshooting

### Local Development Issues
- **Composer issues**: `composer update`
- **NPM issues**: `npm install --legacy-peer-deps`
- **Database error**: `php artisan migrate:fresh --seed`

### Railway Deployment
- **Check logs**: Railway dashboard → Logs tab
- **Database issues**: Railway auto-creates MySQL, check env vars
- **Build failed**: Usually PHP version or missing extension

## 📊 Database Schema

### Key Tables
- `users` - User accounts & authentication
- `predictions` - House price predictions
- `properties` - Property data
- `sessions` - User sessions

Run migrations to create:
```bash
php artisan migrate
```

## 🔄 Development Workflow

```bash
# Pull latest changes
git pull origin main

# Install dependencies
composer install && npm install

# Run migrations
php artisan migrate

# Start dev server
php artisan serve
npm run dev  # In another terminal for asset watching
```

## 📦 Deployment Checklist

- [ ] Code pushed to GitHub main branch
- [ ] Database credentials secured in environment variables
- [ ] APP_KEY generated and set
- [ ] TiDB/MySQL database created
- [ ] Render service configured
- [ ] Environment variables set in Render
- [ ] Application deployed and live

## 🤝 Contributing

Contributions are welcome! Please follow Laravel conventions and best practices.

## 📄 License

MIT License - see LICENSE file for details.

## 👨‍💻 Author

**Ranggard** - GitHub: [@Ranggard](https://github.com/Ranggard)

## 🔗 Links

- Repository: https://github.com/Ranggard/Website-Taksir-Rumah-Bandung
- Live Demo: https://railway.app/ (deploy di sini)
- Railway Docs: https://docs.railway.app/

---

**Made with ❤️ for Bandung Housing Market**
