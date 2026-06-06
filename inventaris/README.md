# 📦 Aplikasi Inventaris RPL 1
**Asesmen Sumatif Akhir Semester Genap 2025/2026**  
Muh Faqihuddin Assholih, S.Kom | XI RPL 1

---

## 🚀 Cara Instalasi

### 1. Persiapan
- Pastikan XAMPP atau Laragon sudah terinstal dan berjalan
- Aktifkan **Apache** dan **MySQL**

### 2. Setup Database
1. Buka **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Buat database baru bernama `inventaris_rpl1`
3. Klik tab **SQL**, lalu salin dan jalankan isi file `database.sql`
4. Database dan data awal akan terbuat otomatis

### 3. Pasang Aplikasi
1. Ekstrak folder `inventaris` ke dalam:
   - XAMPP: `C:\xampp\htdocs\inventaris`
   - Laragon: `C:\laragon\www\inventaris`
2. Edit file `includes/config.php` jika password MySQL Anda bukan kosong:
   ```php
   define('DB_PASS', 'password_anda');
   ```

### 4. Jalankan
Buka browser dan akses:
```
http://localhost/inventaris
```

---

## 🔐 Akun Default

| Role  | Username | Password  |
|-------|----------|-----------|
| Admin | admin    | admin123  |
| User  | budi     | user123   |
| User  | siti     | user123   |

---

## 📁 Struktur File

```
inventaris/
├── index.php               → Redirect otomatis
├── login.php               → Halaman login
├── logout.php              → Proses logout
├── database.sql            → Script database lengkap
│
├── includes/
│   ├── config.php          → Koneksi database & helper
│   ├── auth.php            → Autentikasi & role check
│   ├── sidebar.php         → Komponen sidebar navigasi
│   └── style.css           → CSS Design System
│
├── admin/
│   ├── dashboard.php       → Dashboard admin (statistik)
│   ├── barang.php          → CRUD barang inventaris
│   ├── peminjaman.php      → Kelola peminjaman & pengembalian
│   ├── pengguna.php        → Manajemen akun pengguna
│   └── laporan.php         → Laporan & cetak
│
└── user/
    ├── dashboard.php       → Dashboard pengguna
    ├── barang.php          → Lihat daftar barang
    ├── pinjam.php          → Form peminjaman
    └── riwayat.php         → Riwayat peminjaman saya
```

---

## ✅ Fitur Lengkap

### Admin
- [x] Dashboard dengan statistik real-time
- [x] Tambah / Edit / Hapus barang
- [x] Kelola stok & kondisi barang (baik/rusak)
- [x] Lihat & kelola semua peminjaman
- [x] Konfirmasi pengembalian barang
- [x] Manajemen pengguna
- [x] Laporan peminjaman dengan cetak

### User
- [x] Dashboard personal
- [x] Lihat daftar barang tersedia
- [x] Form peminjaman barang
- [x] Riwayat peminjaman sendiri
- [x] Filter status peminjaman

### Database
- [x] Stored Procedure: `pinjam_barang()` - catat pinjam + kurangi stok
- [x] Stored Procedure: `kembalikan_barang()` - catat kembali + tambah stok
- [x] Function: `status_barang()` - Tersedia / Hampir Habis / Habis

---

## 🎨 Tampilan
- Dark theme modern dengan aksen amber
- Font: Syne (heading) + DM Sans (body) + Space Mono (kode)
- Responsive untuk desktop dan mobile
- Modal popup untuk form tambah/edit
- Bar visualisasi stok barang

---

*Dibuat untuk Asesmen Sumatif Akhir Semester Genap 2025/2026*
