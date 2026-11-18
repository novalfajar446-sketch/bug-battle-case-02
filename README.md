# Studi Kasus 02 (Relasi Buku dan Kategori)

Aplikasi web manajemen buku dan kategori berbasis **Laravel** dan **MySQL** untuk mata kuliah **Pemrograman Web Lanjut**. Studi kasus ini berfokus pada perbaikan relasi Eloquent *one-to-many* (`belongsTo` dan `hasMany`), *foreign key*, validasi *mass assignment*, serta tampilan Blade.

## Prasyarat Sistem
- **PHP** >= 8.2
- **Composer**
- **MySQL / MariaDB** (XAMPP / Laragon)
- **Git**

## Cara Menjalankan di Lokal

1. **Clone repository**
   ```bash
   git clone https://github.com/novalfajar446-sketch/bug-battle-case-02.git
   cd bug-battle-case-02
   ```

2. **Install dependensi Composer**
   ```bash
   composer install
   ```

3. **Konfigurasi file `.env`**
   Salin file `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
   Buat database baru di MySQL bernama `bug_battle_case_02`, lalu sesuaikan baris konfigurasi database pada `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=bug_battle_case_02
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

5. **Migrasi & Seeding Database**
   ```bash
   php artisan migrate
   php artisan db:seed --class=CategorySeeder
   php artisan db:seed --class=BookSeeder
   ```

6. **Menjalankan Pengujian Otomatis (`PHPUnit`)**
   *(Catatan: Jalankan ulang perintah seeder di atas setelah mengeksekusi tes apabila tabel kembali kosong).*
   ```bash
   php artisan test --filter=BookCategoryRelationTest
   ```

7. **Menjalankan Server Lokal**
   ```bash
   php artisan serve
   ```
   Buka browser dan akses **`http://127.0.0.1:8000`**.

---

## Dokumentasi Hasil Pengujian & Tampilan Web

### 1. Pengujian Terminal (`PHPUnit`)
![Hasil PHPUnit Kasus 02](screenshots/ganti-nama-file-test.png)

### 2. Tampilan Aplikasi Web
![Tampilan Web Kasus 02](screenshots/ganti-nama-file-web.png)
