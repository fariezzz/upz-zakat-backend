# UPZ Zakat Unsil — Backend API

> REST API untuk sistem manajemen zakat UPZ Universitas Siliwangi.
> Dibangun dengan **Laravel 12** + **Laravel Sanctum** + **PostgreSQL (Neon)**.

---

## Prasyarat

Pastikan sudah terinstall di komputer:

| Tool | Versi minimal | Catatan |
|---|---|---|
| PHP | 8.2+ | Wajib mengaktifkan ekstensi **`pdo_pgsql`** & **`pgsql`** |
| Composer | 2.x | Package manager PHP |
| Git | - | |

> PostgreSQL **tidak perlu diinstall lokal** — project ini menggunakan [Neon](https://neon.tech) (cloud PostgreSQL). Kamu hanya perlu string koneksi dari Neon.

---

## Setup dari Nol

### 1. Clone Repository

```bash
git clone https://github.com/ndiecyber/UPZ-Backend.git
cd UPZ-Backend
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Aktifkan Ekstensi PostgreSQL di PHP (`php.ini`)

> ⚠️ **PENTING (Troubleshooting `could not find driver (Connection: pgsql)`):**
> Jika saat menjalankan migrasi/server muncul error driver pgsql tidak ditemukan, buka file `php.ini` kamu (cek lokasinya via `php --ini`):
> 
> Hapus tanda titik koma `;` di depan baris berikut:
> ```ini
> extension=pdo_pgsql
> extension=pgsql
> ```
> Setelah itu restart server web / PHP. Verifikasi dengan perintah `php -m | findstr pgsql`.

---

### 4. Konfigurasi Environment

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

---

### 5. Generate Application Key

```bash
php artisan key:generate
```

---

### 6. Jalankan Migrasi & Seeder

```bash
php artisan migrate:fresh --seed
```

Perintah ini akan:
- Membuat semua tabel (`users`, `muzakki`, `mustahik`, `transaksi`, `program_penyaluran`, `jurnals`, dll.)
- Mengisi data awal: admin, muzakki, mustahik, transaksi, program aktif, dan jurnal

**Akun admin default hasil seeder:**
| | |
|---|---|
| Email | `admin@upz-unsil.ac.id` |
| Password | `password` |

---

### 7. Jalankan Server Development

```bash
php artisan serve --port=8000
```

API sekarang berjalan di **http://localhost:8000**

---

## Struktur API

Semua endpoint diawali dengan prefix `/api`.

### Endpoint Publik (Tanpa Auth)

| Method | Endpoint | Deskripsi |
|---|---|---|
| `POST` | `/api/auth/login` | Login user (Admin/Operator), mendapat Bearer token |
| `POST` | `/api/donasi` | Form submit donasi online dari publik |
| `GET` | `/api/public/program` | Daftar program penyaluran aktif untuk halaman utama |
| `GET` | `/api/public/laporan` | Ringkasan laporan keuangan & transparansi untuk halaman utama |

---

### Endpoint Auth & Profil (Butuh Token)

| Method | Endpoint | Deskripsi |
|---|---|---|
| `POST` | `/api/auth/logout` | Logout & revoke token |
| `GET` | `/api/auth/me` | Dapatkan data profil user aktif |
| `PUT` | `/api/auth/profile` | Update nama & email profil |
| `PUT` | `/api/auth/password` | Update kata sandi akun |

---

### Endpoint Dashboard & Manajemen (Butuh Token)

Sertakan header `Authorization: Bearer {token}` di setiap request.

| Resource | Method | Endpoint | Deskripsi |
|---|---|---|---|
| **Dashboard** | `GET` | `/api/dashboard/all?tahun=2026` | Ambil statistik, ringkasan, grafik, transaksi & program sekaligus |
| | `GET` | `/api/dashboard/stats` | Statistik 4 card utama |
| | `GET` | `/api/dashboard/ringkasan-dana` | Agregat penerimaan & penyaluran |
| | `GET` | `/api/dashboard/grafik` | Data grafik bulanan |
| **Muzakki** | `GET, POST` | `/api/muzakki` | List & tambah data muzakki |
| | `PUT, DELETE` | `/api/muzakki/{id}` | Update & hapus data muzakki |
| | `GET` | `/api/muzakki/options` | Option list untuk combobox transaksi |
| **Mustahik** | `GET, POST` | `/api/mustahik` | List & tambah data mustahik |
| | `PUT, DELETE` | `/api/mustahik/{id}` | Update & hapus data mustahik |
| **Program** | `GET, POST` | `/api/program` | List & tambah program penyaluran |
| | `PUT, DELETE` | `/api/program/{id}` | Update & hapus program |
| | `GET` | `/api/program/options` | Option list program aktif |
| **Transaksi** | `GET, POST` | `/api/transaksi/pengumpulan` | Transaksi zakat & infaq masuk |
| | `GET, POST` | `/api/transaksi/penyaluran` | Transaksi penyaluran zakat keluar |
| **Donasi** | `GET` | `/api/donasi` | List donasi online terverifikasi |
| **Laporan** | `GET` | `/api/laporan/ringkasan` | Laporan keuangan tahunan |
| **Jurnal** | `GET, POST` | `/api/jurnal` | List & buat entri jurnal akuntansi |
| | `PUT, DELETE` | `/api/jurnal/{id}` | Update & hapus entri jurnal |

---

## Struktur Database

```
users               — Akun administrator & operator
muzakki             — Data muzakki / donatur
mustahik            — Data mustahik penerima manfaat & asnaf
transaksi           — Data pencatatan penerimaan & pengeluaran dana
program_penyaluran  — Master data program distribusi zakat
jurnals             — Catatan jurnal akuntansi & kas
personal_access_tokens — Token otentikasi Sanctum
```

---

## Koneksi dengan Frontend

Setelah backend berjalan, buka project frontend dan isi file `.env`:

```env
VITE_API_URL=http://localhost:8000/api
```

Lihat repo frontend: [UPZ-Frontend](https://github.com/ndiecyber/UPZ-Frontend)

---

## Perintah Berguna

```bash
# Migrasi ulang dari awal + isi data seeder
php artisan migrate:fresh --seed

# Hanya jalankan migrasi baru (tanpa reset)
php artisan migrate

# Cek status migrasi
php artisan migrate:status

# Clear cache konfigurasi (jalankan setelah ubah .env)
php artisan config:clear

# Lihat semua route API yang terdaftar
php artisan route:list --path=api
```

---

## Tech Stack

- **Laravel 12** — PHP Framework
- **Laravel Sanctum** — Autentikasi Bearer Token
- **PostgreSQL (Neon DB)** — Cloud Relational Database
- **PHP 8.2+**
