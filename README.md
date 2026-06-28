# Sistem Tracer Study - CodeIgniter 3

Sistem pelacakan alumni terintegrasi untuk memenuhi standar **BAN-PT** dan **LAM**, serta mendukung pelaporan **IKU-1** (Indikator Kinerja Utama) ke **PDDikti**.

## 🚀 Fitur Utama

- **Manajemen Alumni**: Database terpusat, pelacakan karir, dan validasi data.
- **Survey Builder**: Pembuatan survei dinamis dengan logika *branching* dan *skip logic*.
- **Survei Alumni & Stakeholder**: Survei otomatis sesuai standar Belmawa dan analisis kesenjangan (*gap analysis*) kompetensi.
- **Evaluasi Kurikulum**: Pemetaan CPL (*Curriculum Learning Outcomes*) dan umpan balik berbasis data.
- **Dashboard IKU-1**: Monitoring real-time dan ekspor data langsung ke format PDDikti.
- **Integrasi PDDikti**: Sinkronisasi otomatis data lulusan dan penelusuran alumni.
- **Multi-Role Access**: Manajemen akses untuk Admin, Prodi, Fakultas, dan Universitas.

## 🛠️ Teknologi

- **Backend**: PHP 7.4+ (CodeIgniter 3.1.x)
- **Database**: MySQL 5.7+ / MariaDB
- **Frontend**: Bootstrap 5, jQuery, DataTables, Chart.js
- **Arsitektur**: HMVC (Hierarchical Model-View-Controller)

## ⚡ Instalasi Cepat

1. **Clone Repository**
   ```bash
   git clone <repository-url>
   cd tracer-study
   ```

2. **Konfigurasi Database**
   - Buat database baru (misal: `tracer_study`).
   - Impor file SQL dari folder `database/migrations/`.
   - Salin `application/config/database.php.example` menjadi `database.php` dan sesuaikan kredensial.

3. **Konfigurasi Aplikasi**
   - Atur `base_url` di `application/config/config.php`.
   - Pastikan folder `application/cache` dan `writable` memiliki izin tulis (755 atau 777).

4. **Akses Aplikasi**
   - Buka browser dan akses `http://localhost/tracer-study`.
   - Login default: `admin` / `admin123` (Harap segera diubah!).

## 📂 Struktur Folder Penting

```
├── application/
│   ├── controllers/    # Logika bisnis (HMVC Modules)
│   ├── models/         # Interaksi Database
│   ├── views/          # Tampilan Antarmuka
│   └── config/         # Konfigurasi Sistem
├── database/
│   └── migrations/     # Skema Database & Seeder
├── docs/               # Dokumentasi (SRS, Manual)
└── assets/             # CSS, JS, Images
```

## 🔒 Keamanan & Audit

- Password di-hash menggunakan `password_hash()` (Bcrypt).
- Proteksi CSRF aktif pada semua form.
- Sistem audit trail mencatat seluruh aktivitas pengguna (Login, Edit, Export).
- Validasi input ketat untuk mencegah SQL Injection dan XSS.

## 📄 Lisensi

Sistem ini dikembangkan untuk keperluan internal institusi pendidikan.

---
*Dibuat dengan ❤️ untuk Kemajuan Pendidikan Tinggi Indonesia*