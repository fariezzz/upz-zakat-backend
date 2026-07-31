# UPZ Zakat Unsil — Backend API

> REST API untuk sistem manajemen zakat UPZ Universitas Siliwangi.
> Dibangun dengan **Laravel 12** + **Laravel Sanctum** + **PostgreSQL (Neon)**.

---

## Prasyarat

Pastikan sudah terinstall di komputer:

| Tool | Versi minimal |
|---|---|
| PHP | 8.2+ |
| Composer | 2.x |
| Git | - |

> PostgreSQL **tidak perlu diinstall lokal** — project ini menggunakan [Neon](https://neon.tech) (cloud PostgreSQL). Kamu hanya perlu string koneksi dari Neon.

---

## Setup dari Nol

### 1. Clone Repository

```bash
git clone https://github.com/fariezzz/upz-zakat-backend.git
cd upz-zakat-backend
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Konfigurasi Environment

Salin file contoh `.env`:

```bash
cp .env.example .env
```

Lalu buka `.env` dan isi bagian database dengan kredensial Neon kamu:

```env
APP_NAME="UPZ Zakat Unsil"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

FRONTEND_URL=http://localhost:5173

DB_CONNECTION=pgsql
DB_HOST=<host-dari-neon>
DB_PORT=5432
DB_DATABASE=<nama-database>
DB_USERNAME=<username>
DB_PASSWORD=endpoint=<endpoint-id>;<password>
DB_SSLMODE=require
```

> **Cara dapat kredensial Neon:**
> 1. Login ke [neon.tech](https://neon.tech)
> 2. Buat project baru (atau pakai yang sudah ada)
> 3. Klik **"Connect"** → pilih **"PHP (PDO)"**
> 4. Salin bagian host, database, username, dan password
>
> **Penting — format `DB_PASSWORD` khusus untuk Neon:**
> Neon memerlukan endpoint ID di depan password agar koneksi berhasil:
> ```
> DB_PASSWORD=endpoint=ep-xxxxx-xxxxx;password-asli-kamu
> ```
> Endpoint ID adalah bagian pertama dari hostname (sebelum `.c-X.`).

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Jalankan Migrasi & Seeder

```bash
php artisan migrate:fresh --seed
```

Perintah ini akan:
- Membuat semua tabel (users, muzakki, transaksi, program_penyaluran, dll.)
- Mengisi data awal: admin, muzakki, transaksi 2024–2025, program aktif

**Akun admin default hasil seeder:**
| | |
|---|---|
| Email | `admin@upz-unsil.ac.id` |
| Password | `password` |

### 6. Jalankan Server

```bash
php artisan serve --port=8000
```

API sekarang berjalan di **http://localhost:8000**

---

## Struktur API

Semua endpoint diawali dengan prefix `/api`.

### Autentikasi (Public)

| Method | Endpoint | Deskripsi |
|---|---|---|
| `POST` | `/api/auth/login` | Login, mendapat Bearer token |

**Body request login:**
```json
{
  "email": "admin@upz-unsil.ac.id",
  "password": "password"
}
```

**Response:**
```json
{
  "token": "1|xxxxxxx",
  "user": { "name": "Admin UPZ", "role": "administrator" }
}
```

### Dashboard (Butuh Auth)

Sertakan header `Authorization: Bearer {token}` di setiap request.

| Method | Endpoint | Deskripsi |
|---|---|---|
| `GET` | `/api/dashboard/stats?tahun=2025` | 4 stat card utama |
| `GET` | `/api/dashboard/ringkasan-dana?tahun=2025` | Data donut chart |
| `GET` | `/api/dashboard/grafik?tahun=2025` | Data line chart per bulan |
| `GET` | `/api/transaksi?limit=5` | Transaksi terbaru |
| `GET` | `/api/program?status=aktif&tahun=2025` | Program penyaluran aktif |
| `POST` | `/api/auth/logout` | Hapus token (logout) |
| `GET` | `/api/auth/me` | Info user yang sedang login |

---

## Struktur Database

```
users               — akun admin
muzakki             — data donatur/pembayar zakat
transaksi           — semua pemasukan & pengeluaran dana
program_penyaluran  — program distribusi dana
personal_access_tokens — token Sanctum
```

---

## Koneksi dengan Frontend

Setelah backend berjalan, buka project frontend dan isi file `.env`:

```env
VITE_API_URL=http://localhost:8000/api
```

Lihat repo frontend: [upz-zakat-frontend](https://github.com/fariezzz/upz-zakat-frontend)

---

## Perintah Berguna

```bash
# Migrasi ulang dari awal + isi data seeder
php artisan migrate:fresh --seed

# Hanya jalankan migrasi baru (tanpa reset)
php artisan migrate

# Clear cache konfigurasi (jalankan setelah ubah .env)
php artisan config:clear

# Lihat semua route API yang terdaftar
php artisan route:list --path=api
```

---

## Tech Stack

- **Laravel 12** — PHP framework
- **Laravel Sanctum** — Autentikasi Bearer token
- **PostgreSQL (Neon)** — Database cloud
- **PHP 8.2+**
