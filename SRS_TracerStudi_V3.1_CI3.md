# SOFTWARE REQUIREMENTS SPECIFICATION (SRS)

## Sistem Tracer Study Perguruan Tinggi

**Versi 3.1 - Revisi Stack CodeIgniter 3**

**27 Juni 2026**

**Tim Pengembangan Tracer Study Kampus**

---

## DAFTAR ISI

- [Pendahuluan](#pendahuluan)
- [Deskripsi Umum](#deskripsi-umum)
- [Spesifikasi Kebutuhan Fungsional](#spesifikasi-kebutuhan-fungsional)
- [Spesifikasi Kebutuhan Non-Fungsional](#spesifikasi-kebutuhan-non-fungsional)
- [Desain Antarmuka Pengguna (UI/UX)](#desain-antarmuka-pengguna-uiux)
- [Arsitektur Sistem, Teknologi & API](#arsitektur-sistem-teknologi--api)
- [Kebutuhan Data, Database & Data Dictionary](#kebutuhan-data-database--data-dictionary)
- [Keamanan Sistem & Threat Model](#keamanan-sistem--threat-model)
- [Pengujian, Kualitas & Traceability](#pengujian-kualitas--traceability)
- [Jadwal, Deliverables & Deployment](#jadwal-deliverables--deployment)
- [Lampiran](#lampiran)

---

## 1. PENDAHULUAN

### 1.1 Tujuan

Dokumen SRS ini mendefinisikan spesifikasi lengkap untuk pengembangan **Sistem Tracer Study Perguruan Tinggi (TS-PT) Versi 3.1** menggunakan stack CodeIgniter 3. Sistem ini dirancang untuk:

- Melacak jejak karir alumni sesuai standar nasional (kohort lulusan 1 dan 2 tahun pasca yudisium)
- Mengevaluasi kesesuaian kompetensi lulusan dengan kebutuhan industri dan pengguna lulusan
- Menyediakan data analitis untuk perbaikan kurikulum berkelanjutan (PPEPP SPMI)
- **Memenuhi pelaporan Indikator Kinerja Utama (IKU-1) ke Kemendikdristek**
- **Mendukung kelengkapan akreditasi BAN-PT (Kriteria 9: Luaran dan Capaian Tridharma)**
- **Mengintegrasikan pertanyaan inti baku Ditjen Belmawa untuk kompilasi data nasional**

### 1.2 Ruang Lingkup

| Aspek | Deskripsi |
|-------|-----------|
| **Nama Sistem** | Tracer Study Perguruan Tinggi (TS-PT) v3.1 |
| **Target Pengguna** | Super Admin, Admin Pusat Karir, Admin Prodi, Dosen/Wali Kelas, Alumni, Reviewer/Auditor, Pengguna Lulusan (Stakeholder/Employer) |
| **Platform** | Web-based (Desktop & Mobile Responsive), Shared Hosting Compatible |
| **Fungsi Utama** | Manajemen kohort alumni, survey baku + kustom, logic branching, analisis IKU-1, pelaporan nasional, evaluasi kurikulum, survey pengguna lulusan |
| **Integrasi** | PDDikti NeoFeeder, SISTER, Sistem Tracer Study Nasional (tracerstudy.kemdikbud.go.id) |

### 1.3 Definisi & Akronim

| Istilah | Definisi |
|---------|----------|
| **CI3** | CodeIgniter 3 - Framework PHP |
| **Tracer Study** | Studi pelacakan alumni untuk mengevaluasi outcome pendiditian sesuai SE Dirjen Dikti |
| **Logic Jump** | Mekanisme percabangan pertanyaan berdasarkan jawaban responden |
| **Kohort** | Kelompok lulusan tahun yang sama (bukan angkatan masuk) yang menjadi target tracer study |
| **IKU-1** | Indikator Kinerja Utama 1: Persentase lulusan dengan pekerjaan/studi/wirausaha |
| **CPL** | Capaian Pembelajaran Lulusan |
| **Pengguna Lulusan** | Stakeholder/employer yang menggunakan jasa lulusan (DUDI) |
| **Pertanyaan Inti** | Pertanyaan wajib baku Ditjen Belmawa yang tidak boleh diubah/dihapus |
| **PPEPP** | Penetapan, Pelaksanaan, Evaluasi, Pengendalian, Peningkatan (siklus SPMI) |
| **UMP** | Upah Minimum Provinsi |
| **BRD** | Business Rules Document |
| **DFD** | Data Flow Diagram |
| **STRIDE** | Spoofing, Tampering, Repudiation, Information Disclosure, Denial of Service, Elevation of Privilege |

### 1.4 Referensi

- IEEE Std 830-1998: Recommended Practice for Software Requirements Specifications
- **Permendikbud No. 3 Tahun 2020 tentang Standar Nasional Pendidikan Tinggi**
- **SE Dirjen Pembelajaran dan Kemahasiswaan No. 471/B/SE/2017 tentang Pelaksanaan Tracer Study**
- **SE Dirjen Dikti No. 2100/E2/TU/2020 tentang Pelaksanaan Tracer Study di PT**
- **SE Dirjen Dikti No. 1997/E2/WA.01.04/2022 tentang Pelaporan Tracer Study**
- **Kepmen No. 754/P/2020 tentang Indikator Kinerja Utama PTN**
- **PerBAN-PT No. 5 Tahun 2024 tentang Pemantauan dan Evaluasi Mutu PT**
- Panduan Penyusunan Kurikulum Pendidikan Tinggi 2020 (Kemendikbud)
- Buku Pedoman IKU PTN 2021 (Kemendikbudristek)
- **CodeIgniter 3 User Guide**
- **Bootstrap 5 Documentation**
- **jQuery 3.6 Documentation**
- **WCAG 2.1 Level AA Guidelines**

### 1.5 Business Rules Document (BRD)

Business Rules adalah aturan bisnis yang mengatur perilaku sistem dan tidak dapat dilanggar. Programmer **WAJIB** mengimplementasikan semua rule berikut.

#### 1.5.1 Rule Alumni & Kohort

| Rule ID | Aturan | Impact | Validasi |
|---------|--------|--------|----------|
| **BR-ALM-001** | Alumni hanya dapat mengisi survey untuk **kohort aktif** (lulusan 1-2 tahun pasca yudisium dari tahun berjalan) | SUR-AL-010 | Cek kohort.status = 'aktif' dan tahun_lulus dalam range |
| **BR-ALM-002** | NIM bersifat **immutable** setelah registrasi. Tidak boleh diubah oleh siapapun termasuk Super Admin | ALM-001 | Database constraint UNIQUE, UI readonly |
| **BR-ALM-003** | Alumni yang statusnya **'belum_bekerja'** tidak boleh mengisi field gaji, nama_perusahaan, atau jabatan | ALM-011 | Frontend disable + Backend validation |
| **BR-ALM-004** | **Masa tunggu** dihitung otomatis: bulan(tanggal_mulai_kerja - tanggal_yudisium). Jika tanggal_mulai_kerja < tanggal_yudisium, reject | ALM-009 | Trigger/Service validation |
| **BR-ALM-005** | Alumni yang belum **verifikasi email** (status pending) **boleh** mengisi survey, tapi data masuk status pending_verifikasi dan **tidak** masuk perhitungan IKU-1 | ALM-001, IKU-005 | Cek users.email_verified_at IS NOT NULL untuk IKU |
| **BR-ALM-006** | Import Excel alumni wajib memiliki kolom: NIM, Nama, Prodi, Tahun Lulus. Jika salah satu kosong, **skip row** dan log error | ALM-003 | Validation rule required |
| **BR-ALM-007** | Alumni yang sudah mengisi survey untuk kohort tertentu **tidak boleh** mengisi lagi untuk kohort yang sama (sensus, bukan sampling) | SUR-AL-010 | UNIQUE constraint (survey_id, alumni_id) |
| **BR-ALM-008** | Update profil alumni (pekerjaan, gaji) **memicu recalculation IKU-1** untuk kohort terkait | ALM-002, IKU-002 | Event listener AlumniUpdated → RecalculateIkuJob |
| **BR-ALM-009** | Data alumni dari PDDikti memiliki **precedence** lebih tinggi dari data manual. Jika conflict NIM, data PDDikti menang | INT-001 | Merge strategy: PDDikti wins |
| **BR-ALM-010** | Alumni dengan status nonaktif (meninggal/drop out setelah lulus) **dikecualikan** dari target populasi kohort | ALM-001 | Filter status != 'nonaktif' |

#### 1.5.2 Rule Survey & Pertanyaan

| Rule ID | Aturan | Impact | Validasi |
|---------|--------|--------|----------|
| **BR-SUR-001** | Pertanyaan dengan is_belma_inti = TRUE **tidak dapat dihapus, diubah teks, atau diubah urutan** oleh Admin Prodi. Hanya Super Admin yang bisa modify dengan approval log | SUR-AL-007 | Policy SurveyPolicy::delete(), audit log |
| **BR-SUR-002** | Pertanyaan inti wajib ada minimal **20 pertanyaan** sesuai lampiran A. Jika kurang, sistem **reject publish** | SUR-AL-007 | Pre-publish validation |
| **BR-SUR-003** | Logic Jump **tidak boleh** membuat circular reference (A→B→A). Sistem wajib mendeteksi dan reject saat save | SUR-AL-002 | Graph cycle detection algorithm |
| **BR-SUR-004** | Survey dengan status published **tidak boleh** diubah struktur pertanyaannya (hanya bisa di-duplicate ke draft baru) | SUR-AL-003 | State machine: published → locked |
| **BR-SUR-005** | Alumni yang mengisi survey dan **menutup browser** sebelum submit, data tersimpan di localStorage (PWA) dan bisa dilanjutkan dalam **7 hari** | SUR-AL-011 | localStorage + IndexedDB, TTL 7 hari |
| **BR-SUR-006** | Stakeholder survey **wajib** linked ke alumni tertentu (via alumni_id) atau ke prodi (via prodi_id). Tidak boleh orphan | SUR-ST-005 | Foreign key constraint |
| **BR-SUR-007** | Rating kompetensi oleh stakeholder (1-5) akan **di-average** dengan rating alumni sendiri dengan bobot 60:40 (stakeholder:alumni) untuk gap analysis | SUR-ST-002, KUR-003 | Weighted average formula |
| **BR-SUR-008** | Jika alumni memilih "Lanjut Studi" dan "Bekerja" bersamaan, **prioritas** dihitung sebagai "Bekerja" untuk IKU-1 (karena bobot lebih tinggi) | IKU-008 | Business logic: bekerja > wirausaha > studi > belum |

#### 1.5.3 Rule IKU-1 & Pelaporan

| Rule ID | Aturan | Impact | Validasi |
|---------|--------|--------|----------|
| **BR-IKU-001** | IKU-1 hanya valid jika **response rate ≥ minimum threshold** per kohort (lihat ALM-007). Jika tidak, skor = 0 dan status belum_tercapai | IKU-006, IKU-003 | Auto-calculation + flag |
| **BR-IKU-002** | Sumber kebenaran untuk bobot IKU adalah **gaji_aktual** (jika ada) > **gaji_range** (fallback). Jika keduanya null, bobot = 0 | IKU-002 | COALESCE logic |
| **BR-IKU-003** | Verifikasi data (V&V) rate < 80% mengakibatkan **pengurangan skor IKU sebesar 20%** dari skor kalkulasi | IKU-005 | Formula: final_score = raw_score * min(vv_rate, 1.0) |
| **BR-IKU-004** | Perhitungan IKU-1 dijalankan **otomatis** setiap hari jam 02:00 WIB via Cron Job (CLI), dan **real-time** saat data alumni di-update | IKU-001 | Cron + Controller trigger |
| **BR-IKU-005** | Export template Belmawa hanya boleh dilakukan oleh **Admin Pusat Karir** atau **Super Admin** | IKU-004 | Gate export.belmawa |
| **BR-IKU-006** | Data yang sudah di-export ke Belmawa dan **status = 'dikirim'** tidak boleh diubah lagi (immutable untuk audit) | IKU-007 | Database constraint + UI lock |
| **BR-IKU-007** | UMP yang digunakan adalah UMP **provinsi domisili alumni** saat bekerja, bukan UMP kampus | IKU-002 | Lookup table ump_provinsi |

#### 1.5.4 Rule Keamanan & Akses

| Rule ID | Aturan | Impact | Validasi |
|---------|--------|--------|----------|
| **BR-SEC-001** | Activity log **tidak dapat dihapus** oleh siapapun. Retention minimum 5 tahun | SEC-010 | Database DELETE blocked, append-only |
| **BR-SEC-002** | Data PII (NIK, NIK, gaji_aktual, no_hp) **wajib dienkripsi** AES-256 at rest. Di log, hanya tampilkan hash/masked | SEC-006, SEC-009 | CI3 Encryption Library, mutator |
| **BR-SEC-003** | API Key untuk integrasi PDDikti **rotasi otomatis** setiap 90 hari. Notifikasi 7 hari sebelum expired | SEC-008, INT-001 | Cron + Notification |
| **BR-SEC-004** | Session timeout 30 menit idle. Jika alumni sedang mengisi survey, **extend session** setiap interaksi (heartbeat) | AUTH-005 | CI3 Session + JS heartbeat |
| **BR-SEC-005** | Rate limit login: 5 attempt per IP per menit. Jika exceeded, **lock IP 30 menit** dan kirim alert ke Super Admin | SEC-007 | Throttle library + Notification |

#### 1.5.5 Rule Notifikasi & Reminder

| Rule ID | Aturan | Impact | Validasi |
|---------|--------|--------|----------|
| **BR-NOT-001** | Reminder survey dikirim **H-7, H-3, H-1** sebelum tgl_selesai survey ke alumni yang belum mengisi | ALM-005 | Cron + Queue (database-based) |
| **BR-NOT-002** | Notifikasi WhatsApp **fallback** ke Email jika WhatsApp gagal (status gagal setelah 3 retry) | ALM-005 | Queue + Dead letter |
| **BR-NOT-003** | Admin Prodi hanya menerima notifikasi untuk **prodi sendiri**. Admin Pusat Karir menerima notifikasi PT-wide | AUTH | Scope notifikasi by role |

---

## 2. DESKRIPSI UMUM

### 2.1 Perspektif Produk

Sistem TS-PT v3.1 adalah aplikasi web berbasis **PHP CodeIgniter 3** yang berfungsi sebagai platform end-to-end untuk:

- **Manajemen Kohort Alumni**: Pendataan berdasarkan tahun lulus (kohort), bukan angkatan masuk
- **Distribusi Survey**: Kuesioner alumni dengan mekanisme logic branching, termasuk pertanyaan inti baku Belmawa
- **Survey Pengguna Lulusan**: Kuesioner kepuasan dan kesesuaian kompetensi dari DUDI/employer
- **Analisis IKU-1 Otomatis**: Perhitungan skor IKU-1 dengan pembobotan gaji dan masa tunggu
- **Pelaporan Nasional**: Export data sesuai template Excel untuk upload ke tracerstudy.kemdikbud.go.id
- **Analisis & Pelaporan**: Dashboard analitik dengan visualisasi grafik interaktif
- **Evaluasi Kurikulum**: Pemetaan gap CPL dengan kebutuhan industri berbasis data survey ganda (alumni + stakeholder)

### 2.2 Fungsi Produk

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    SISTEM TRACER STUDY v3.1 (CodeIgniter 3)                │
├─────────────┬─────────────┬─────────────┬─────────────┬─────────────────────┤
│   Alumni    │    Admin      │   Survey    │   Laporan   │ Integrasi Nasional  │
│ Management  │  Dashboard    │   Engine    │  & Analisis │  & IKU Dashboard    │
├─────────────┼─────────────┼─────────────┼─────────────┼─────────────────────┤
│ • Registrasi│ • User Mgmt   │ • Builder   │ • Grafik    │ • Sinkron PDDikti   │
│ • Profil    │ • Kohort Mgmt │ • Logic Jump│ • Export PDF│ • Export Template   │
│ • Tracking  │ • Kurikulum   │ • Validasi  │ • Dashboard │   Belmawa           │
│ • Kohort    │   Mgmt        │ • Mobile    │ • Real-time │ • IKU-1 Calculator  │
│ Assignment  │ • Survey Mgmt │ • Pertanyaan│ • Filter    │ • Verifikasi Data   │
│             │ • Stakeholder │   Inti Baku │   Data      │ • API Integration   │
│             │   Survey Mgmt │ • Preview   │ • Perbandingan│ • Audit Trail     │
└─────────────┴─────────────┴─────────────┴─────────────┴─────────────────────┘
```

### 2.3 Karakteristik Pengguna

| Role | Deskripsi | Hak Akses |
|------|-----------|-----------|
| **Super Admin** | Administrator sistem utama | Full access, manajemen tenant, konfigurasi integrasi nasional |
| **Admin Pusat Karir** | Operator pusat karir tingkat PT | CRUD survey, kelola kohort, pelaporan nasional, verifikasi data IKU |
| **Admin Prodi** | Operator per Program Studi | CRUD survey prodi sendiri, view laporan prodi, kelola alumni prodi |
| **Dosen/Wali Kelas** | Pengajar & pembimbing | View laporan, input data alumni, bantu verifikasi |
| **Alumni** | Lulusan perguruan tinggi | Isi survey, update profil, view sertifikat pengisian |
| **Pengguna Lulusan** | DUDI/Employer/Stakeholder | Isi survey kepuasan, penilaian kompetensi alumni |
| **Reviewer/Auditor** | Pihak eksternal (akreditasi/BAN-PT) | View laporan read-only, export bukti IKU-1 |

### 2.4 Batasan & Asumsi

- **Browser Support**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **Koneksi**: Minimal 1 Mbps untuk pengisian survey
- **Device**: Desktop, tablet, smartphone (responsive)
- **Bahasa**: Indonesia (primary), English (secondary untuk laporan internasional)
- **Integrasi**: Sistem memiliki API untuk sinkronisasi dengan PDDikti NeoFeeder
- **Compliance**: Wajib menggunakan pertanyaan inti Belmawa yang tidak dapat dihapus
- **Kohort**: Sistem hanya mengizinkan tracer pada kohort lulusan 1-2 tahun pasca yudisium untuk IKU-1
- **Hosting**: Shared hosting compatible (cPanel/Plesk/DirectAdmin), PHP 7.4+, MySQL 5.7+

---

## 3. SPESIFIKASI KEBUTUHAN FUNGSIONAL

### 3.1 Modul Manajemen Alumni (ALM)

| ID | Kebutuhan | Prioritas | Status | Business Rule |
|----|-----------|-----------|--------|---------------|
| ALM-001 | Registrasi alumni dengan validasi NIM & tahun lulus | High | Mandatory | BR-ALM-002, BR-ALM-005, BR-ALM-010 |
| ALM-002 | Update profil alumni (pekerjaan, kontak, domisili) | High | Mandatory | BR-ALM-003, BR-ALM-008 |
| ALM-003 | Import data alumni dari Excel/CSV dengan template baku | High | Mandatory | BR-ALM-006 |
| ALM-004 | Pencarian & filter alumni (multi-kriteria: kohort, prodi, status kerja, gaji) | High | Mandatory | - |
| ALM-005 | Notifikasi reminder via email/WhatsApp/Telegram untuk mengisi survey | High | Mandatory | BR-NOT-001, BR-NOT-002, BR-NOT-003 |
| ALM-006 | Tracking status pengisian survey per alumni (in_progress/completed/abandoned) | High | Mandatory | - |
| ALM-007 | Penetapan kohort lulusan otomatis berdasarkan tahun yudisium | High | Mandatory | BR-ALM-001 |
| ALM-008 | Sinkronisasi data alumni dari PDDikti NeoFeeder (auto-import) | High | Mandatory | BR-ALM-009 |
| ALM-009 | Tracking masa tunggu kerja (bulan sejak yudisium sampai kerja pertama) | High | Mandatory | BR-ALM-004 |
| ALM-010 | Verifikasi data alumni oleh admin (validasi kebenaran pekerjaan/gaji) | High | Mandatory | BR-IKU-003 |
| ALM-011 | Pengelompokan alumni berdasarkan status: Bekerja, Wirausaha, Lanjut Studi, Belum Bekerja | High | Mandatory | BR-ALM-003, BR-SUR-008 |

**Detail ALM-007 (Kohort Management):**

```
Kohort Management:
├── Auto-generate kohort berdasarkan tahun lulus dari PDDikti
├── Kohort Aktif: Lulusan 1 tahun dan 2 tahun sebelum tahun berjalan
├── Target Populasi: Seluruh lulusan (sensus, bukan sampling)
├── Monitoring Progress: Persentase pengisian per kohort per prodi
├── Minimum Responden Threshold:
│   ├── 1-100 lulusan: 80%
│   ├── 101-500: 60%
│   ├── 501-1000: 35%
│   ├── 1001-2000: 20%
│   ├── 2001-3000: 15%
│   └── >3000: 10%
└── Alert jika belum memenuhi minimum responden
```

**Detail ALM-009 (Masa Tunggu Tracking):**

```
Perhitungan Masa Tunggu:
├── Input: Tanggal yudisium, Tanggal mulai kerja/studi/wirausaha
├── Output: Masa tunggu dalam bulan
├── Kategori:
│   ├── ≤ 6 bulan (bobot tinggi untuk IKU)
│   ├── 7-12 bulan (bobot sedang)
│   └── > 12 bulan (bobot rendah)
└── Integrasi otomatis ke perhitungan IKU-1
```

### 3.2 Modul Manajemen Survey Alumni (SUR-AL)

| ID | Kebutuhan | Prioritas | Status | Business Rule |
|----|-----------|-----------|--------|---------------|
| SUR-AL-001 | Builder pertanyaan dengan tipe: Short Answer, Multiple Choice, Long Answer, Dropdown, Checkbox, Rating, Date, Number | High | Mandatory | - |
| SUR-AL-002 | Logic Jump/Conditional Branching antar pertanyaan | High | Mandatory | BR-SUR-003 |
| SUR-AL-003 | Pengaturan waktu aktif/non-aktif survey per kohort | High | Mandatory | BR-SUR-004 |
| SUR-AL-004 | Duplikasi survey template | Medium | Mandatory | - |
| SUR-AL-005 | Preview survey sebelum publish | High | Mandatory | - |
| SUR-AL-006 | Manajemen section/bab dalam satu survey | Medium | Mandatory | - |
| SUR-AL-007 | Pertanyaan Inti Baku Belmawa (locked, tidak dapat dihapus/diubah) | High | Mandatory | BR-SUR-001, BR-SUR-002 |
| SUR-AL-008 | Pertanyaan tambahan kustom oleh Prodi/PT | Medium | Mandatory | - |
| SUR-AL-009 | Auto-fill data alumni (NIM, Nama, Prodi, Tahun Lulus) dari database | High | Mandatory | - |
| SUR-AL-010 | Survey berbasis kohort (hanya kohort aktif yang dapat mengisi) | High | Mandatory | BR-ALM-001, BR-ALM-007 |
| SUR-AL-011 | Pengisian survey mobile-friendly dengan progress bar dan auto-save | High | Mandatory | BR-SUR-005 |

### 3.3 Modul Survey Pengguna Lulusan / Stakeholder (SUR-ST)

| ID | Kebutuhan | Prioritas | Status | Business Rule |
|----|-----------|-----------|--------|---------------|
| SUR-ST-001 | Registrasi pengguna lulusan (DUDI/Employer) dengan validasi | High | Mandatory | - |
| SUR-ST-002 | Penilaian kesesuaian kompetensi alumni oleh employer (Rating 1-5) | High | Mandatory | BR-SUR-007 |
| SUR-ST-003 | Survey kepuasan pengguna lulusan terhadap kinerja alumni | High | Mandatory | - |
| SUR-ST-004 | Rekomendasi kompetensi yang dibutuhkan industri | High | Mandatory | - |
| SUR-ST-005 | Linking survey stakeholder dengan alumni tertentu/prodi | Medium | Mandatory | BR-SUR-006 |
| SUR-ST-006 | Dashboard gap analysis: CPL Prodi vs Kompetensi Industri | High | Mandatory | BR-SUR-007 |

### 3.4 Modul Manajemen Kurikulum (KUR)

| ID | Kebutuhan | Prioritas | Status | Business Rule |
|----|-----------|-----------|--------|---------------|
| KUR-001 | Input & kelola kurikulum per Program Studi | High | Mandatory | - |
| KUR-002 | Pemetaan Capaian Pembelajaran Lulusan (CPL) | High | Mandatory | - |
| KUR-003 | Penilaian kesesuaian CPL dengan kebutuhan industri (alumni + stakeholder) | High | Mandatory | BR-SUR-007 |
| KUR-004 | Perbandingan kurikulum antar periode (tahun) | Medium | Mandatory | - |
| KUR-005 | Rekomendasi perbaikan kurikulum berbasis data survey | Medium | Mandatory | - |
| KUR-006 | Pemetaan CPL terhadap SN-Dikti dan KKNI | High | Mandatory | - |
| KUR-007 | Evaluasi CPL berbasis hasil tracer study (PPEPP) | High | Mandatory | - |

### 3.5 Modul IKU-1 & Pelaporan Nasional (IKU)

| ID | Kebutuhan | Prioritas | Status | Business Rule |
|----|-----------|-----------|--------|---------------|
| IKU-001 | Dashboard IKU-1 real-time dengan perhitungan otomatis | High | Mandatory | BR-IKU-004 |
| IKU-002 | Perhitungan bobot responden berdasarkan gaji vs UMP dan masa tunggu | High | Mandatory | BR-IKU-002, BR-IKU-007 |
| IKU-003 | Monitoring minimum responden per kohort | High | Mandatory | BR-IKU-001 |
| IKU-004 | Export data Excel sesuai template tracerstudy.kemdikbud.go.id | High | Mandatory | BR-IKU-005 |
| IKU-005 | Verifikasi dan validasi data (V&V) dengan flag status | High | Mandatory | BR-IKU-003 |
| IKU-006 | Perhitungan response rate dan alert threshold | High | Mandatory | BR-IKU-001 |
| IKU-007 | Laporan triwulan otomatis (Q1, Q2, Q3, Q4) | High | Mandatory | BR-IKU-006 |
| IKU-008 | Rekonsiliasi data ganda (alumni bekerja + melanjutkan studi) | Medium | Mandatory | BR-SUR-008 |

**Detail IKU-002 (Perhitungan Bobot IKU-1):**

```
FORMULA IKU-1:
IKU-1 = (Σ Bobot Responden / Total Responden Memenuhi Minimum) × 100

PEMBOBOTAN BEKERJA:
┌─────────────────┬─────────────────┬─────────────────┐
│   Gaji / Masa   │    ≤ 6 bulan    │   7-12 bulan    │
│     Tunggu      │                 │                 │
├─────────────────┼─────────────────┼─────────────────┤
│   ≥ 1.2 × UMP   │      1.0        │      0.8        │
│   < 1.2 × UMP   │      0.8        │      0.6        │
└─────────────────┴─────────────────┴─────────────────┘

PEMBOBOTAN MELANJUTKAN STUDI: 0.6
PEMBOBOTAN WIRAUSAHA: 1.0 (jika omzet ≥ UMP) / 0.8 (jika < UMP)

VERIFIKASI & VALIDASI:
├── Kirim link verifikasi ke responden dengan bobot maksimal
├── Jika hasil V&V < 80%, dikenakan pengurangan skor IKU
└── Audit trail untuk setiap perubahan status verifikasi
```

### 3.6 Modul Pelaporan & Analisis (RPT)

| ID | Kebutuhan | Prioritas | Status |
|----|-----------|-----------|--------|
| RPT-001 | Dashboard real-time dengan grafik interaktif | High | Mandatory |
| RPT-002 | Grafik: Pie, Bar, Line, Doughnut, Radar, Stacked Bar | High | Mandatory |
| RPT-003 | Filter laporan (periode/kohort, prodi, angkatan, status kerja, gaji) | High | Mandatory |
| RPT-004 | Export laporan ke PDF (via DOMPDF/MPDF) | High | Mandatory |
| RPT-005 | Export data ke Excel/CSV | Medium | Mandatory |
| RPT-006 | Perbandingan data antar periode/kohort | Medium | Mandatory |
| RPT-007 | Laporan evaluasi diri untuk akreditasi BAN-PT (Kriteria 9) | High | Mandatory |
| RPT-008 | Laporan IKU-1 untuk Kontrak Kinerja PTN | High | Mandatory |
| RPT-009 | Laporan PPEPP: analisis kebutuhan, evaluasi kurikulum, rekomendasi | High | Mandatory |
| RPT-010 | Heatmap analisis kompetensi per prodi | Medium | Mandatory |

**Jenis Grafik yang Dihasilkan:**

| No | Grafik | Kegunaan | Library |
|----|--------|----------|---------|
| 1 | Pie Chart | Distribusi status kerja alumni | Chart.js |
| 2 | Bar Chart | Rata-rata gaji per prodi/tahun | Chart.js |
| 3 | Line Chart | Tren jumlah responden per kohort | Chart.js |
| 4 | Doughnut Chart | Persentase kesesuaian kompetensi | Chart.js |
| 5 | Radar Chart | Profil kompetensi lulusan vs kebutuhan industri | Chart.js |
| 6 | Stacked Bar | Perbandingan data multi-periode/kohort | Chart.js |
| 7 | Gauge Chart | Real-time IKU-1 score vs target | Chart.js |
| 8 | Heatmap | Gap analysis CPL per mata kuliah | D3.js/Heatmap.js |

### 3.7 Modul Autentikasi & Otorisasi (AUTH)

| ID | Kebutuhan | Prioritas | Status | Business Rule |
|----|-----------|-----------|--------|---------------|
| AUTH-001 | Login multi-role dengan session management | High | Mandatory | BR-SEC-004 |
| AUTH-002 | Password hashing (bcrypt/Argon2) | High | Mandatory | - |
| AUTH-003 | Forgot password via email | Medium | Mandatory | - |
| AUTH-004 | Activity log & audit trail | High | Mandatory | BR-SEC-001 |
| AUTH-005 | Session timeout (30 menit idle) | High | Mandatory | BR-SEC-004 |
| AUTH-006 | Single Sign-On (SSO) dengan sistem kampus (LDAP/Active Directory) | Medium | Optional | - |
| AUTH-007 | Two-Factor Authentication (2FA) untuk admin pusat karir | Medium | Optional | - |
| AUTH-008 | API Key management untuk integrasi eksternal | High | Mandatory | BR-SEC-003 |

### 3.8 Modul Integrasi & API (INT)

| ID | Kebutuhan | Prioritas | Status | Business Rule |
|----|-----------|-----------|--------|---------------|
| INT-001 | REST API untuk sinkronisasi data alumni dengan PDDikti NeoFeeder | High | Mandatory | BR-ALM-009, BR-SEC-003 |
| INT-002 | Export Excel template sesuai format tracerstudy.kemdikbud.go.id | High | Mandatory | BR-IKU-005, BR-IKU-006 |
| INT-003 | Webhook untuk notifikasi real-time (WhatsApp/Telegram/Email) | Medium | Mandatory | BR-NOT-002 |
| INT-004 | API untuk pengambilan data IKU-1 oleh SISTER/PDDikti | High | Mandatory | - |
| INT-005 | Integrasi dengan sistem akademik kampus (Siakad) | Medium | Optional | - |

### 3.9 Error Handling & Exception Matrix

Programmer wajib mengimplementasikan handling berikut untuk setiap modul:

#### 3.9.1 Global Error Handling

| Kode Error | Skenario | HTTP Status | Response ke User | Log Level | Tindakan Sistem |
|------------|----------|-------------|------------------|-----------|-----------------|
| **ERR-GLOBAL-001** | Uncaught Exception | 500 | "Terjadi kesalahan sistem. Tim kami telah diberitahu." | ERROR | Email alert ke admin, Rollback DB transaction |
| **ERR-GLOBAL-002** | Validation Exception | 422 | Daftar field yang invalid | WARNING | Return validation errors JSON |
| **ERR-GLOBAL-003** | Authentication Exception | 401 | "Sesi Anda telah berakhir. Silakan login kembali." | WARNING | Redirect login, clear session |
| **ERR-GLOBAL-004** | Authorization Exception | 403 | "Anda tidak memiliki akses ke halaman ini." | WARNING | Log access denied, audit trail |
| **ERR-GLOBAL-005** | Not Found Exception | 404 | "Data yang Anda cari tidak ditemukan." | INFO | - |
| **ERR-GLOBAL-006** | Rate Limit Exceeded | 429 | "Terlalu banyak percobaan. Silakan coba lagi dalam [X] menit." | WARNING | BR-SEC-005 |
| **ERR-GLOBAL-007** | Maintenance Mode | 503 | "Sistem sedang dalam pemeliharaan." | INFO | - |

#### 3.9.2 Modul Alumni Error Handling

| Kode Error | Skenario | HTTP Status | Response | Log | Tindakan |
|------------|----------|-------------|----------|-----|----------|
| **ERR-ALM-001** | NIM duplikat saat import | 422 | "NIM [X] sudah terdaftar pada baris [Y]" | WARNING | Skip row, continue import, generate report |
| **ERR-ALM-002** | PDDikti API timeout | 504 | "Sinkronisasi sedang diproses. Anda akan diberitahu saat selesai." | ERROR | Cron retry 3x, notifikasi admin |
| **ERR-ALM-003** | Format Excel tidak valid | 422 | "Template tidak sesuai. Silakan unduh template baku." | WARNING | Reject file, offer download template |
| **ERR-ALM-004** | Alumni update gaji tapi belum verifikasi email | 200 | "Data tersimpan, namun belum masuk perhitungan IKU. Verifikasi email Anda." | INFO | BR-ALM-005 |
| **ERR-ALM-005** | Masa tunggu negatif (kerja sebelum lulus) | 422 | "Tanggal mulai kerja tidak boleh sebelum tanggal yudisium." | WARNING | BR-ALM-004 |

#### 3.9.3 Modul Survey Error Handling

| Kode Error | Skenario | HTTP Status | Response | Log | Tindakan |
|------------|----------|-------------|----------|-----|----------|
| **ERR-SUR-001** | Circular reference logic jump | 422 | "Logic jump tidak boleh berputar. Periksa kembali alur pertanyaan." | WARNING | BR-SUR-003 |
| **ERR-SUR-002** | Survey published tapi admin coba edit | 403 | "Survey yang sudah dipublish tidak dapat diubah. Duplikat ke draft baru." | WARNING | BR-SUR-004 |
| **ERR-SUR-003** | Alumni coba isi survey kohort non-aktif | 403 | "Survey untuk kohort Anda sudah ditutup." | INFO | BR-ALM-001 |
| **ERR-SUR-004** | Alumni sudah pernah isi survey kohort ini | 409 | "Anda sudah mengisi survey untuk periode ini." | INFO | BR-ALM-007 |
| **ERR-SUR-005** | Pertanyaan wajib tidak dijawab | 422 | "Mohon lengkapi pertanyaan wajib [X]" | INFO | - |
| **ERR-SUR-006** | Browser offline, auto-save gagal | 200 (local) | "Data tersimpan lokal. Akan dikirim saat online." | INFO | BR-SUR-005, localStorage |
| **ERR-SUR-007** | Admin coba hapus pertanyaan inti | 403 | "Pertanyaan inti Belmawa tidak dapat dihapus." | WARNING | BR-SUR-001 |

#### 3.9.4 Modul IKU Error Handling

| Kode Error | Skenario | HTTP Status | Response | Log | Tindakan |
|------------|----------|-------------|----------|-----|----------|
| **ERR-IKU-001** | IKU calculation gagal (data tidak lengkap) | 500 | "Perhitungan IKU sedang diperbaiki." | ERROR | Skip invalid records, flag for review |
| **ERR-IKU-002** | Response rate < minimum, export IKU dicegah | 403 | "Minimum responden belum tercapai. IKU tidak valid." | WARNING | BR-IKU-001 |
| **ERR-IKU-003** | Export Belmawa tapi data sudah dikirim | 403 | "Data sudah dikirim ke Belmawa dan tidak dapat diubah." | WARNING | BR-IKU-006 |
| **ERR-IKU-004** | UMP untuk provinsi tidak ditemukan | 500 | "Data UMP tidak tersedia. Hubungi Super Admin." | ERROR | Fallback ke UMP nasional, alert admin |

#### 3.9.5 Modul Keamanan Error Handling

| Kode Error | Skenario | HTTP Status | Response | Log | Tindakan |
|------------|----------|-------------|----------|-----|----------|
| **ERR-SEC-001** | Login gagal 5x | 429 | "Akun terkunci 30 menit." | WARNING | BR-SEC-005, notifikasi Super Admin |
| **ERR-SEC-002** | API Key expired | 401 | "API Key tidak valid atau sudah expired." | WARNING | BR-SEC-003, redirect ke rotasi key |
| **ERR-SEC-003** | XSS attempt detected | 403 | "Akses ditolak." | CRITICAL | Block IP, email alert, audit trail |
| **ERR-SEC-004** | CSRF token mismatch | 419 | "Sesi tidak valid. Refresh halaman." | WARNING | Regenerate token |

### 3.10 State Diagram & Workflow

#### 3.10.1 Survey Lifecycle State Machine

```
┌─────────────┐
│   DRAFT     │
│ (Editable)  │
└──────┬──────┘
       │ Admin klik "Publish"
       │ Validasi: min 20 pertanyaan inti
       ▼
┌─────────────┐     ┌─────────────┐
│  PUBLISHED  │◄────│   ACTIVE    │
│ (Read-only  │      │(Respondable)│
│  structure) │      └──────┬──────┘
└──────┬──────┘             │ tgl_selesai <
       │                   │ today
       │ tgl_mulai <=      ▼
       │ today <=      ┌─────────────┐     ┌─────────────┐
       │ tgl_selesai   │   CLOSED    │────►│  ARCHIVED   │
       │               │ (No more    │       │ (Immutable) │
       │               │  responses) │       └─────────────┘
       │               └──────┬──────┘
       │                      │ Admin klik "Re-open"
       │                      │ (hanya jika belum
       │                      │ dikirim ke Belmawa)
       └──────────────────────┘
```

**Transition Rules:**
- DRAFT → PUBLISHED: Wajib validasi BR-SUR-002 (min 20 pertanyaan inti). Jika gagal, return ERR-SUR-002.
- PUBLISHED → ACTIVE: Otomatis oleh cron job pada tgl_mulai.
- ACTIVE → CLOSED: Otomatis oleh cron job pada tgl_selesai, atau manual oleh admin.
- CLOSED → ACTIVE: Hanya jika status_iku != 'dikirim'. Jika sudah dikirim, return ERR-IKU-003.
- CLOSED → ARCHIVED: Manual atau auto setelah 90 hari.
- ARCHIVED: Tidak ada transisi keluar. Data immutable.

#### 3.10.2 Alumni Status Workflow

```
┌─────────────┐    Registrasi    ┌─────────────┐
│    BARU     │ ────────────────►│   PENDING   │
│  (Import)   │                  │ (Email not  │
└─────────────┘                  │  verified)  │
                                 └──────┬──────┘
                                        │ Verifikasi email
                                        ▼
                                 ┌─────────────┐
                                 │    AKTIF    │
                                 │ (Verified,  │
                                 │ can survey) │
                                 └──────┬──────┘
                                        │ Update profil
                                        │ (pekerjaan, gaji)
                                        ▼
                                 ┌─────────────┐
                                 │   TERDATA   │
                                 │ (Complete  │
                                 │  profile)   │
                                 └──────┬──────┘
                                        │ Isi survey
                                        ▼
                                 ┌─────────────┐
                                 │  RESPONDED  │
                                 │  (Survey    │
                                 │  completed) │
                                 └──────┬──────┘
                                        │ Admin verifikasi
                                        ▼
                                 ┌─────────────┐
                                 │  VERIFIED   │
                                 │ (Data masuk │
                                 │  IKU calc)  │
                                 └─────────────┘
```

#### 3.10.3 IKU-1 Calculation State Machine

```
┌─────────────┐    Auto-calc    ┌─────────────┐
│    DRAFT    │ ───────────────►│  CALCULATED │
│   (Initial) │   daily 02:00   │  (Has score)│
│             │      WIB        │             │
└─────────────┘                 └──────┬──────┘
                                      │ Admin review
                                      ▼
                               ┌─────────────┐
                               │   REVIEWED  │
                               │  (Admin     │
                               │   checked)  │
                               └──────┬──────┘
                                      │ Admin approve
                                      ▼
                               ┌─────────────┐
                               │ TERVERIFIKASI│
                               │ (Ready for  │
                               │   export)   │
                               └──────┬──────┘
                                      │ Export to Belmawa
                                      ▼
                               ┌─────────────┐
                               │   DIKIRIM   │
                               │  (Immutable)│
                               └─────────────┘
```

#### 3.10.4 Verifikasi Data Workflow

```
┌─────────────┐   Admin review   ┌─────────────┐
│   PENDING   │ ───────────────►│ TERVERIFIKASI│
│ (Submitted) │                  │ (Valid for  │
└─────────────┘                  │    IKU)     │
                                 └─────────────┘
                                 ▲
                                 │ Admin reject
                                 │
                           ┌─────────────┐
                           │   DITOLAK   │   Alumni revise   ┌─────────────┐
                           │  (With note) │ ────────────────►│   PENDING   │
                           └─────────────┘                  │ (Re-submit) │
                                                          └─────────────┘
```

---

## 4. SPESIFIKASI KEBUTUHAN NON-FUNGSIONAL

### 4.1 Performa (PERF)

| ID | Kebutuhan | Target | Measurement Method |
|----|-----------|--------|-------------------|
| PERF-001 | Load time halaman utama | ≤ 2 detik | Browser DevTools / GTmetrix |
| PERF-002 | Submit survey (≤ 50 pertanyaan) | ≤ 3 detik | Browser DevTools |
| PERF-003 | Generate laporan PDF (≤ 1000 record) | ≤ 8 detik | Benchmark script |
| PERF-004 | Generate IKU-1 dashboard real-time | ≤ 5 detik | File cache hit > 95% |
| PERF-005 | Concurrent users | ≥ 1000 simultan | Apache JMeter / Loader.io |
| PERF-006 | Uptime sistem | ≥ 99.9% | Uptime monitoring (UptimeRobot) |
| PERF-007 | Export Excel template nasional (5000+ record) | ≤ 15 detik | Cron + background processing |
| PERF-008 | Sinkronisasi batch PDDikti (10.000 record) | ≤ 5 menit | Cron job benchmark |

**Performance Baseline Detail:**
- **Halaman Dashboard Admin**: Load time diukur dari first byte sampai Chart.js selesai render. Target: < 2 detik dengan file-based cache CI3.
- **Survey Form Mobile**: Load time diukur dari tap link sampai pertanyaan pertama render. Target: < 1.5 detik dengan lazy loading.
- **Export Excel 5000 record**: Diproses via **Cron Job** (background). User mendapat notifikasi "Sedang diproses" dan email saat selesai. Bukan real-time blocking.
- **Concurrent Users 1000**: 1000 user login bersamaan, 500 user isi survey bersamaan, 50 admin generate laporan bersamaan.

### 4.2 Keamanan (SEC)

| ID | Kebutuhan | Implementasi |
|----|-----------|-------------|
| SEC-001 | SQL Injection prevention | CI3 Query Builder, parameterized query, input sanitization |
| SEC-002 | XSS prevention | Output escaping (htmlspecialchars), CSP headers, HTML Purifier |
| SEC-003 | CSRF protection | CI3 CSRF token, security library |
| SEC-004 | Password security | Bcrypt/Argon2 hashing, min 12 karakter, complexity check |
| SEC-005 | Role-based access control (RBAC) | CI3 Hooks + custom library otorisasi per controller |
| SEC-006 | Data encryption (PII) | CI3 Encryption Library (AES-256) untuk data sensitif (NIK, gaji_aktual, no_hp) |
| SEC-007 | Rate limiting | Database-based throttle (tabel login_attempts), max 5 per IP per menit |
| SEC-008 | API Security | CI3 REST Controller + API Key custom |
| SEC-009 | Data Privacy Compliance | Sesuai UU PDP (Perlindungan Data Pribadi) |
| SEC-010 | Audit Trail Immutable | Log tidak dapat dihapus, hanya append |

### 4.3 Ketersediaan & Reliabilitas (REL)

| ID | Kebutuhan | Target |
|----|-----------|--------|
| REL-001 | Backup database otomatis | Harian (incremental via cron), Mingguan (full via phpMyAdmin/cron) |
| REL-002 | Recovery Time Objective (RTO) | ≤ 4 jam (shared hosting constraint) |
| REL-003 | Recovery Point Objective (RPO) | ≤ 24 jam (shared hosting constraint) |
| REL-004 | Failover server untuk pelaporan nasional | Manual backup restore (shared hosting constraint) |
| REL-005 | Data retention policy | 5 tahun untuk kebutuhan audit akreditasi |

### 4.4 Usability (USA)

| ID | Kebutuhan | Kriteria |
|----|-----------|----------|
| USA-001 | Mobile responsive | Bootstrap 5 grid system, breakpoint sm/md/lg/xl |
| USA-002 | Accessibility | WCAG 2.1 Level AA, kontras warna, ARIA labels, screen reader |
| USA-003 | Browser compatibility | Chrome, Firefox, Safari, Edge (2 versi terakhir) |
| USA-004 | Bahasa | Indonesia (UI), English (laporan opsional) |
| USA-005 | Offline capability untuk pengisian survey | localStorage + sessionStorage (jQuery-based), data tersimpan lokal |
| USA-006 | Progressive Web App (PWA) | Service Worker (Workbox via jQuery wrapper), installable, push notification |

### 4.5 Maintainability (MTN)

| ID | Kebutuhan | Implementasi |
|----|-----------|-------------|
| MTN-001 | Modular code | CI3 HMVC (Modular Extensions - MX), separate libraries |
| MTN-002 | Code documentation | PHPDoc standard, inline comments |
| MTN-003 | Database versioning | CI3 Migrations, manual SQL changelog |
| MTN-004 | Error logging | CI3 Log Library + email alert ke admin |
| MTN-005 | Unit & Integration Testing | PHPUnit (standalone) atau CI3 Unit Test Library, minimum 80% code coverage |
| MTN-006 | CI/CD Pipeline | Manual deployment via FTP/Git, atau GitHub Actions untuk testing saja |

### 4.6 Compliance & Standarisasi (CMP)

| ID | Kebutuhan | Kriteria |
|----|-----------|----------|
| CMP-001 | Compliance dengan SE Dirjen Dikti | Pertanyaan inti baku, format pelaporan, kohort 1-2 tahun |
| CMP-002 | Compliance dengan BAN-PT | Kriteria 9: Luaran dan Capaian, dokumen evaluasi diri |
| CMP-003 | Compliance dengan IKU-PTN | Perhitungan bobot, verifikasi data, minimum responden |
| CMP-004 | Compliance dengan SPMI/PPEPP | Siklus penetapan, pelaksanaan, evaluasi, pengendalian, peningkatan |
| CMP-005 | Standar Nasional Pendidikan Tinggi (SN-Dikti) | Pemetaan CPL, evaluasi kurikulum |

---

## 5. DESAIN ANTARMUKA PENGGUNA (UI/UX)

### 5.1 Design System

**Color Palette:**

- Primary: #1e3a8a (Biru Tua) - Header, Primary Button, Navbar
- Secondary: #3b82f6 (Biru) - Links, Active State, Accent
- Success: #10b981 (Hijau) - Success Message, Submit, IKU Tercapai
- Warning: #f59e0b (Kuning) - Warning, Pending, Minimum Responden Belum Tercapai
- Danger: #ef4444 (Merah) - Error, Delete, Required, IKU Tidak Tercapai
- Info: #06b6d4 (Cyan) - Info Badge, Kohort Aktif
- Light: #f8fafc (Abu Muda) - Background
- Dark: #1e293b (Abu Tua) - Text Primary
- IKU-Gauge: #8b5cf6 (Ungu) - IKU Score Indicator

**Typography:**
- Primary Font: Inter / Roboto (Google Fonts)
- Heading: 600-700 weight
- Body: 400 weight
- Base size: 16px (1rem)

**Spacing System (Bootstrap 5):**
- Base unit: 0.25rem (4px)
- Scale: 1, 2, 3, 4, 5 (0.25rem - 3rem)

### 5.2 Wireframe Layout

**A. Dashboard Admin Pusat Karir (Desktop)**

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ [LOGO]  Dashboard  Alumni  Survey  IKU-1  Kurikulum  Laporan  [User ▼]     │
├──────────┬──────────────────────────────────────────────────────────────────┤
│          │ ┌─────────────┐ ┌─────────────┐ ┌──────────┐ ┌─────────────┐    │
│ NAVIGATION│ │ Total Alumni│ │  Responden  │ │ Response │ │ IKU-1 Score │    │
│          │ │   1,245     │ │    892      │ │  71.6%   │ │ 78.5 / 100  │    │
│ • Dashboard│ └─────────────┘ └─────────────┘ └──────────┘ └─────────────┘    │
│ • Kohort  │ ┌─────────────────────────────────────────────────────────────┐    │
│ • Alumni  │ │         GRAFIK IKU-1 GAUGE (Real-time vs Target)          │    │
│ • Survey  │ │              [Gauge Chart: 78.5%]                         │    │
│ • IKU-1   │ └─────────────────────────────────────────────────────────────┘    │
│ • Kurikulum│ ┌────────────────────┐ ┌────────────────────┐                    │
│ • Laporan │ │  GRAFIK STATUS KERJA │ │  GRAFIK KOMPETENSI │                    │
│ • Stakeholder│    [Pie Chart]     │ │    [Radar Chart]   │                    │
│ • Pengaturan│ └────────────────────┘ └────────────────────┘                    │
│          │ ┌─────────────────────────────────────────────────────────┐        │
│          │ │    TABEL KOHORT AKTIF & PROGRESS MINIMUM RESPONDEN    │        │
│          │ │ [Prodi A: 85% ✓] [Prodi B: 45% ⚠] [Prodi C: 70% ✓]   │        │
│          │ └─────────────────────────────────────────────────────────┘        │
└──────────┴──────────────────────────────────────────────────────────────────┘
```

**B. Survey Form (Mobile - Alumni View)**

```
┌─────────────────────────┐
│ ◀ Tracer Study 2026     │
├─────────────────────────┤
│                         │
│  Pertanyaan 3 dari 15   │
│  ████████░░░░░░░░░░ 20% │
│  Auto-save aktif ✓      │
│                         │
│  Apakah Anda sudah      │
│  bekerja saat ini?      │
│                         │
│  ○ Sudah bekerja        │
│  ○ Belum bekerja        │
│  ○ Wirausaha            │
│  ○ Lanjut studi         │
│                         │
│  [ Kembali ] [Lanjut ▶] │
│                         │
└─────────────────────────┘
```

**C. IKU-1 Dashboard (Desktop)**

```
┌─────────────────────────────────────────────────────────────────────────────┐
│              IKU-1 DASHBOARD - TAHUN 2026                                   │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│ ┌─────────────────────────────┐ ┌─────────────────────────────────────┐     │
│ │      GAUGE IKU-1 SCORE      │ │        PEMBOBOTAN RESPONDEN         │     │
│ │                             │ │ ┌─────────┬────────┬────────┐         │     │
│ │     [███████░░░] 78.5       │ │ │ Gaji/   │ ≤6 bln │ 7-12   │         │     │
│ │     Target: 80.0            │ │ │ Tunggu  │        │ bln    │         │     │
│ │                             │ │ ├─────────┼────────┼────────┤         │     │
│ │   Status: HAMBAT ⚠          │ │ │ ≥1.2UMP │  1.0   │  0.8   │         │     │
│ │                             │ │ │ <1.2UMP │  0.8   │  0.6   │         │     │
│ └─────────────────────────────┘ └─────────────────────────────────────┘     │
│                                                                             │
│ ┌─────────────────────────────────────────────────────────────────────┐     │
│ │              PROGRESS KOHORT & MINIMUM RESPONDEN                    │     │
│ │ ┌──────────┬──────────┬──────────┬──────────┬──────────┐           │     │
│ │ │  Kohort  │ Lulusan  │Responden │ Response │  Status  │           │     │
│ │ │   2024   │   250    │   200    │  80.0%   │ ✓ Tercapai│          │     │
│ │ │   2023   │   300    │   150    │  50.0%   │ ✗ Belum  │           │     │
│ │ └──────────┴──────────┴──────────┴──────────┴──────────┘           │     │
│ └─────────────────────────────────────────────────────────────────────┘     │
│                                                                             │
│  [Export Template Belmawa] [Verifikasi Data] [Laporan Triwulan]            │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

**D. Survey Builder - Logic Jump (Desktop)**

```
┌─────────────────────────────────────────────────────────────────────────────┐
│       SURVEY BUILDER: Tracer Study 2024    [?] Help                       │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│ [Sidebar: Pertanyaan]  [Canvas: Logic Flow]                                 │
│ ┌─────────────────┐    ┌─────────────────────────────┐                    │
│ │ Q1. Status      │───►│ [Jawab: Sudah] ──▶ Q2A      │                    │
│ │    Kerja        │    │ [Jawab: Belum] ──▶ Q2B      │                    │
│ │ [Multiple] 🔒   │    │ [Jawab: Wirausaha] ──▶ Q2C  │                    │
│ │ (Inti Belmawa)  │    │ [Jawab: Lanjut Studi] ──▶Q2D│                    │
│ └─────────────────┘    └─────────────────────────────┘                    │
│ ┌─────────────────┐                                                         │
│ │ Q2A. Bidang     │                                                         │
│ │    Pekerjaan    │                                                         │
│ │ [Multiple] 🔒   │                                                         │
│ └─────────────────┘                                                         │
│                                                                             │
│ Legend: 🔒 = Pertanyaan Inti (Tidak dapat dihapus)                        │
│                                                                             │
│ [+ Tambah Pertanyaan] [Simpan] [Preview] [Publish] [Export Template]        │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 5.3 Interaksi & Feedback (SweetAlert + Toast)

| Skenario | Tipe Alert | Pesan |
|----------|------------|-------|
| Submit survey berhasil | Success | "Terima kasih! Survey berhasil dikirim. Data Anda membantu perbaikan kampus." |
| Validasi gagal | Warning | "Mohon lengkapi semua pertanyaan wajib (termasuk pertanyaan inti Belmawa)." |
| Logic jump ter-trigger | Info | "Pertanyaan selanjutnya disesuaikan berdasarkan jawaban Anda…" |
| Session timeout | Error | "Sesi Anda telah berakhir. Silakan login kembali." |
| Export PDF selesai | Success | "Laporan berhasil diunduh." |
| Minimum responden tercapai | Success | "Selamat! Target minimum responden untuk Prodi X telah tercapai (85%)." |
| Verifikasi data berhasil | Info | "Data alumni telah terverifikasi dan masuk perhitungan IKU-1." |
| Auto-save offline | Info | "Data tersimpan lokal. Akan dikirim saat koneksi tersedia." |

---

## 6. ARSITEKTUR SISTEM, TEKNOLOGI & API

### 6.1 Stack Teknologi

| Layer | Teknologi | Versi | Fungsi |
|-------|-----------|-------|--------|
| **Backend** | PHP | 7.4+ / 8.0+ | Server-side scripting (shared hosting compatible) |
| **Framework** | CodeIgniter | 3.1.x | HMVC architecture, Query Builder, Session, Form Validation |
| **Frontend CSS** | Bootstrap | 5.3.x | Responsive UI framework |
| **Frontend JS** | jQuery | 3.6.x | DOM manipulation, AJAX, event handling |
| **Validation** | jQuery Validation + CI3 Form Validation | 1.19.x / Native | Client & server-side validation |
| **Charting** | Chart.js + D3.js | 4.x / 7.x | Data visualization, heatmap |
| **PDF Export** | DOMPDF / MPDF | 2.x / 1.x | PDF generation |
| **Excel Export** | PhpSpreadsheet | 1.x / 2.x | Excel/CSV generation (PHP 7.4 compatible) |
| **Database** | MySQL / MariaDB | 5.7+ / 10.3+ | Data storage (shared hosting compatible) |
| **Cache** | CI3 File-based Cache | Native | Session, cache, query result cache |
| **Queue** | CI3 Cron + Database Queue | Native | Background job (email, export, sinkronisasi) |
| **Web Server** | Apache / Nginx | Native | HTTP server (shared hosting) |
| **API** | CI3 REST Controller (custom/library) | Native | API authentication via API Key |
| **Search** | MySQL Full-text Search | Native | Full-text search alumni (LIKE + MATCH AGAINST) |
| **Monitoring** | CI3 Log + Email Alert | Native | Error logging, email notification |
| **PWA** | Service Worker (Workbox CDN) | - | Offline capability via jQuery + localStorage |

### 6.2 Arsitektur HMVC (CodeIgniter 3)

```
application/
├── config/
│   ├── config.php
│   ├── database.php
│   ├── routes.php
│   ├── hooks.php
│   ├── autoload.php
│   ├── encryption.php
│   ├── email.php
│   └── constants.php
├── core/
│   ├── MY_Controller.php
│   ├── MY_Model.php
│   └── MY_Input.php
├── hooks/
│   ├── AuthHook.php
│   ├── AuditHook.php
│   └── MaintenanceHook.php
├── libraries/
│   ├── IkuCalculator.php
│   ├── SurveyLogic.php
│   ├── PdfGenerator.php
│   ├── ExcelExport.php
│   ├── BelmawaTemplate.php
│   ├── Notification.php
│   ├── PddiktiSync.php
│   ├── Encryption.php (CI3 Native)
│   ├── Session.php (CI3 Native)
│   ├── Form_validation.php (CI3 Native)
│   ├── Upload.php (CI3 Native)
│   ├── Email.php (CI3 Native)
│   └── REST_Controller.php (3rd party)
├── helpers/
│   ├── custom_helper.php
│   ├── auth_helper.php
│   ├── date_helper.php
│   └── security_helper.php
├── models/
│   ├── User_model.php
│   ├── Alumni_model.php
│   ├── Kohort_model.php
│   ├── Program_studi_model.php
│   ├── Survey_model.php
│   ├── Survey_question_model.php
│   ├── Survey_logic_model.php
│   ├── Survey_response_model.php
│   ├── Survey_answer_model.php
│   ├── Stakeholder_model.php
│   ├── Stakeholder_survey_model.php
│   ├── Kurikulum_model.php
│   ├── Cpl_model.php
│   ├── Iku_calculation_model.php
│   ├── Activity_log_model.php
│   ├── Verifikasi_data_model.php
│   ├── Notification_model.php
│   └── Ump_provinsi_model.php
├── controllers/
│   ├── Auth.php
│   ├── Dashboard.php
│   ├── Alumni.php
│   ├── Kohort.php
│   ├── Survey.php
│   ├── Survey_builder.php
│   ├── Stakeholder.php
│   ├── Kurikulum.php
│   ├── Laporan.php
│   ├── Iku.php
│   ├── Api/
│   │   ├── Alumni_api.php
│   │   ├── Iku_api.php
│   │   └── Sync_api.php
│   └── Webhook.php
├── modules/
│   ├── alumni/
│   │   ├── controllers/
│   │   ├── models/
│   │   └── views/
│   ├── survey/
│   │   ├── controllers/
│   │   ├── models/
│   │   └── views/
│   └── iku/
│       ├── controllers/
│       ├── models/
│       └── views/
└── views/
    ├── layouts/
    │   ├── header.php
    │   ├── footer.php
    │   ├── sidebar.php
    │   ├── auth_header.php
    │   └── auth_footer.php
    ├── dashboard/
    ├── alumni/
    ├── survey/
    ├── iku/
    ├── laporan/
    └── stakeholder/

assets/
├── css/
│   ├── bootstrap.min.css
│   ├── custom.css
│   └── sweetalert2.min.css
├── js/
│   ├── jquery.min.js
│   ├── jquery.validate.min.js
│   ├── bootstrap.bundle.min.js
│   ├── chart.js
│   ├── d3.min.js
│   ├── sweetalert2.min.js
│   ├── survey-builder.js (jQuery)
│   ├── survey-form.js (jQuery + localStorage)
│   ├── dashboard.js (jQuery)
│   └── pwa.js (jQuery + Service Worker)
├── images/
└── uploads/
    ├── avatars/
    ├── exports/
    └── bukti/

system/ (CI3 Core)
└── ...

database/
├── migrations/ (manual SQL atau CI3 Migrations)
└── seeds/

cron/
├── iku_calculate.php (CLI Controller)
├── reminder.php (CLI Controller)
├── pddikti_sync.php (CLI Controller)
├── export_processor.php (CLI Controller)
└── api_key_rotate.php (CLI Controller)
```

### 6.3 Database Schema (ERD Conceptual)

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│     users       │     │    alumni       │     │ program_studi │
├─────────────────┤     ├─────────────────┤     ├─────────────────┤
│ id (PK)         │     │ id (PK)         │     │ id (PK)         │
│ username        │     │ user_id (FK)    │◄────┤ kode_prodi      │
│ email           │     │ nim             │     │ nama_prodi      │
│ password_hash   │     │ nama            │     │ fakultas_id     │
│ role            │◄────┤ prodi_id (FK)   │────►│ jenjang         │
│ prodi_id (FK)   │     │ kohort_id (FK)  │────►└─────────────────┘
│ status          │     │ tahun_lulus     │
└─────────────────┘     │ status_kerja    │
                       │ gaji_range      │
                       │ gaji_aktual     │
                       │ masa_tunggu     │
                       │ verifikasi_status│
                       └─────────────────┘
                              │
                              │
                              ▼
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│    kohorts      │     │survey_responses │     │    surveys      │
├─────────────────┤     ├─────────────────┤     ├─────────────────┤
│ id (PK)         │◄────┤ id (PK)         │     │ id (PK)         │
│ tahun_lulus     │     │ alumni_id (FK)  │     │ judul           │
│ target_populasi │     │ survey_id (FK)  │────►│ slug            │
│ min_responden   │     │ submitted_at    │     │ prodi_id (FK)   │
│ status_aktif    │     │ status          │     │ kohort_id (FK)  │
└─────────────────┘     │ completion_time │     │ status          │
                        └─────────────────┘     └─────────────────┘
                              │
                              │
                              ▼
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│survey_questions │     │  survey_logic   │     │ survey_answers  │
├─────────────────┤     ├─────────────────┤     ├─────────────────┤
│ id (PK)         │     │ id (PK)         │     │ id (PK)         │
│ survey_id (FK)  │◄────┤ question_id(FK) │     │ response_id(FK) │
│ question_text   │     │ answer_value    │     │ question_id(FK) │
│ type            │     │ target_q_id(FK) │     │ answer_value    │
│ is_belma_inti   │     │ condition       │     └─────────────────┘
│ order           │     └─────────────────┘
└─────────────────┘

┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│  stakeholders   │     │stakeholder_surveys│    │ iku_calculations│
├─────────────────┤     ├─────────────────┤     ├─────────────────┤
│ id (PK)         │     │ id (PK)         │     │ id (PK)         │
│ nama_institusi  │     │ stakeholder_id  │     │ kohort_id (FK)  │
│ bidang_industri │     │ alumni_id (FK)  │     │ prodi_id (FK)   │
│ email           │     │ rating_kompetensi│    │ total_responden │
│ no_hp           │     │ kesesuaian_cpl  │     │ iku_score       │
└─────────────────┘     │ rekomendasi     │     │ status_iku      │
                        └─────────────────┘     └─────────────────┘

┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   kurikulum     │     │      cpl        │     │  activity_logs  │
├─────────────────┤     ├─────────────────┤     ├─────────────────┤
│ id (PK)         │     │ id (PK)         │     │ id (PK)         │
│ prodi_id (FK)   │     │ prodi_id (FK)   │     │ user_id (FK)    │
│ kode_mk         │     │ kode_cpl        │     │ action          │
│ nama_mk         │     │ deskripsi       │     │ module          │
│ sks             │     │ sn_dikti_ref    │     │ description     │
│ semester        │     │ kkni_ref        │     │ ip_address      │
│ cpl_mapping     │     │ created_at      │     │ user_agent      │
└─────────────────┘     └─────────────────┘     └─────────────────┘
```

### 6.4 API Specification (OpenAPI 3.0 - Adapted CI3)

#### 6.4.1 Authentication

```yaml
openapi: 3.0.3
info:
  title: TS-PT API (CI3)
  version: 3.1.0
paths:
  /api/auth/login:
    post:
      summary: Login user
      requestBody:
        content:
          application/json:
            schema:
              type: object
              properties:
                email: { type: string, format: email }
                password: { type: string, minLength: 12 }
                device_name: { type: string }
      responses:
        200:
          description: Login successful
          content:
            application/json:
              schema:
                type: object
                properties:
                  token: { type: string }
                  user: { $ref: '#/components/schemas/User' }
        401:
          description: Invalid credentials
        429:
          description: Too many attempts (BR-SEC-005)

  /api/auth/logout:
    post:
      summary: Logout user
      security:
        - apiKeyAuth: []
      responses:
        200:
          description: Logout successful

  /api/auth/refresh:
    post:
      summary: Refresh token (re-login required in CI3)
      security:
        - apiKeyAuth: []
      responses:
        200:
          description: Token refreshed
```

#### 6.4.2 Alumni API

```yaml
  /api/alumni:
    get:
      summary: List alumni with pagination & filter
      security: [ apiKeyAuth: [] ]
      parameters:
        - name: kohort_id
          in: query
          schema: { type: integer }
        - name: prodi_id
          in: query
          schema: { type: integer }
        - name: status_kerja
          in: query
          schema: { type: string, enum: [bekerja, wirausaha, lanjut_studi, belum_bekerja] }
        - name: search
          in: query
          schema: { type: string }
        - name: page
          in: query
          schema: { type: integer, default: 1 }
        - name: per_page
          in: query
          schema: { type: integer, default: 20 }
      responses:
        200:
          description: List of alumni

    post:
      summary: Create new alumni
      security: [ apiKeyAuth: [] ]
      requestBody:
        content:
          application/json:
            schema: { $ref: '#/components/schemas/AlumniStoreRequest' }
      responses:
        201:
          description: Alumni created
        422:
          description: Validation error (ERR-ALM-001)

  /api/alumni/{id}:
    get:
      summary: Get alumni detail
      security: [ apiKeyAuth: [] ]
      parameters:
        - name: id
          in: path
          required: true
          schema: { type: integer }
      responses:
        200:
          description: Alumni detail
        404:
          description: Alumni not found (ERR-GLOBAL-005)

    put:
      summary: Update alumni
      security: [ apiKeyAuth: [] ]
      requestBody:
        content:
          application/json:
            schema: { $ref: '#/components/schemas/AlumniUpdateRequest' }
      responses:
        200:
          description: Alumni updated (triggers BR-ALM-008)
        403:
          description: Access denied (ERR-GLOBAL-004)

    delete:
      summary: Delete alumni (soft delete)
      security: [ apiKeyAuth: [] ]
      responses:
        200:
          description: Alumni deleted

  /api/alumni/import:
    post:
      summary: Import alumni from Excel
      security: [ apiKeyAuth: [] ]
      requestBody:
        content:
          multipart/form-data:
            schema:
              type: object
              properties:
                file: { type: string, format: binary }
                prodi_id: { type: integer }
      responses:
        202:
          description: Import queued (background processing via cron)
          content:
            application/json:
              schema:
                type: object
                properties:
                  job_id: { type: string }
                  status_url: { type: string }
        422:
          description: Invalid file format (ERR-ALM-003)

  /api/alumni/sync/pddikti:
    post:
      summary: Sync alumni from PDDikti NeoFeeder
      security: [ apiKeyAuth: [] ]
      requestBody:
        content:
          application/json:
            schema:
              type: object
              properties:
                tahun_lulus: { type: integer }
                prodi_id: { type: integer }
      responses:
        202:
          description: Sync queued (cron job)
        504:
          description: PDDikti timeout (ERR-ALM-002)
```

#### 6.4.3 Survey API

```yaml
  /api/surveys:
    get:
      summary: List surveys
      security: [ apiKeyAuth: [] ]
      parameters:
        - name: status
          in: query
          schema: { type: string, enum: [draft, published, active, closed, archived] }
        - name: prodi_id
          in: query
          schema: { type: integer }
      responses:
        200:
          description: List of surveys

    post:
      summary: Create survey
      security: [ apiKeyAuth: [] ]
      requestBody:
        content:
          application/json:
            schema: { $ref: '#/components/schemas/SurveyStoreRequest' }
      responses:
        201:
          description: Survey created with auto-generated pertanyaan inti

  /api/surveys/{id}/publish:
    post:
      summary: Publish survey
      security: [ apiKeyAuth: [] ]
      responses:
        200:
          description: Survey published
        422:
          description: Validation failed - min 20 pertanyaan inti (ERR-SUR-002)
        403:
          description: Cannot edit published survey (ERR-SUR-002)

  /api/surveys/{id}/questions:
    get:
      summary: List questions
      security: [ apiKeyAuth: [] ]
      responses:
        200:
          description: List of questions

    post:
      summary: Add question
      security: [ apiKeyAuth: [] ]
      requestBody:
        content:
          application/json:
            schema: { $ref: '#/components/schemas/QuestionStoreRequest' }
      responses:
        201:
          description: Question added
        403:
          description: Cannot modify published survey (ERR-SUR-002)

  /api/surveys/{id}/questions/{qid}:
    delete:
      summary: Delete question
      security: [ apiKeyAuth: [] ]
      responses:
        200:
          description: Question deleted
        403:
          description: Pertanyaan inti cannot be deleted (ERR-SUR-007)

  /api/surveys/{id}/logic:
    post:
      summary: Add logic jump
      security: [ apiKeyAuth: [] ]
      requestBody:
        content:
          application/json:
            schema: { $ref: '#/components/schemas/LogicJumpRequest' }
      responses:
        201:
          description: Logic jump created
        422:
          description: Circular reference detected (ERR-SUR-001)

  /api/surveys/{id}/responses:
    post:
      summary: Submit survey response
      security: [ apiKeyAuth: [] ]
      requestBody:
        content:
          application/json:
            schema: { $ref: '#/components/schemas/SurveyResponseRequest' }
      responses:
        201:
          description: Response submitted
        403:
          description: Kohort not active (ERR-SUR-003)
        409:
          description: Already responded (ERR-SUR-004)
        422:
          description: Required questions not answered (ERR-SUR-005)
```

#### 6.4.4 IKU API

```yaml
  /api/iku/calculate:
    post:
      summary: Trigger IKU-1 calculation
      security: [ apiKeyAuth: [] ]
      requestBody:
        content:
          application/json:
            schema:
              type: object
              properties:
                kohort_id: { type: integer }
                prodi_id: { type: integer }
      responses:
        202:
          description: Calculation queued (cron job)
        500:
          description: Calculation failed (ERR-IKU-001)

  /api/iku/dashboard:
    get:
      summary: Get IKU-1 dashboard data
      security: [ apiKeyAuth: [] ]
      parameters:
        - name: tahun
          in: query
          schema: { type: integer }
        - name: prodi_id
          in: query
          schema: { type: integer }
      responses:
        200:
          description: IKU dashboard data with gauge score

  /api/iku/export/belmawa:
    post:
      summary: Export Belmawa template
      security: [ apiKeyAuth: [] ]
      requestBody:
        content:
          application/json:
            schema:
              type: object
              properties:
                kohort_id: { type: integer }
                prodi_id: { type: integer }
      responses:
        202:
          description: Export queued, notification when ready
        403:
          description: Minimum responden not met (ERR-IKU-002)
        403:
          description: Data already sent (ERR-IKU-003)

  /api/iku/verifikasi:
    post:
      summary: Verify alumni data
      security: [ apiKeyAuth: [] ]
      requestBody:
        content:
          application/json:
            schema:
              type: object
              properties:
                alumni_id: { type: integer }
                status: { type: string, enum: [terverifikasi, ditolak] }
                keterangan: { type: string }
      responses:
        200:
          description: Verification updated
```

#### 6.4.5 Webhook API

```yaml
  /api/webhooks/pddikti:
    post:
      summary: PDDikti webhook callback
      security: [ apiKeyAuth: [] ]
      requestBody:
        content:
          application/json:
            schema:
              type: object
              properties:
                event: { type: string }
                data: { type: object }
      responses:
        200:
          description: Webhook processed

  /api/webhooks/whatsapp:
    post:
      summary: WhatsApp delivery status callback
      security: [ apiKeyAuth: [] ]
      requestBody:
        content:
          application/json:
            schema:
              type: object
              properties:
                message_id: { type: string }
                status: { type: string, enum: [terkirim, gagal, dibaca] }
      responses:
        200:
          description: Status updated
```

#### 6.4.6 Schemas

```yaml
components:
  schemas:
    User:
      type: object
      properties:
        id: { type: integer }
        username: { type: string }
        email: { type: string, format: email }
        role: { type: string, enum: [super_admin, admin_pusat_karir, admin_prodi, dosen, alumni, stakeholder, reviewer] }
        prodi_id: { type: integer, nullable: true }
        nama_lengkap: { type: string }
        status: { type: string, enum: [aktif, nonaktif, pending] }

    Alumni:
      type: object
      properties:
        id: { type: integer }
        nim: { type: string }
        nama: { type: string }
        prodi_id: { type: integer }
        kohort_id: { type: integer }
        tahun_lulus: { type: integer }
        email: { type: string }
        status_kerja: { type: string }
        gaji_range: { type: string }
        masa_tunggu_bulan: { type: integer }
        verifikasi_status: { type: string }

    AlumniStoreRequest:
      type: object
      required: [nim, nama, prodi_id, tahun_lulus]
      properties:
        nim: { type: string, maxLength: 20 }
        nama: { type: string, maxLength: 100 }
        prodi_id: { type: integer }
        tahun_lulus: { type: integer, minimum: 2000 }
        email: { type: string, format: email }
        no_hp: { type: string, pattern: '^[0-9]{10,15}$' }

    SurveyStoreRequest:
      type: object
      required: [judul, kohort_id]
      properties:
        judul: { type: string, maxLength: 200 }
        deskripsi: { type: string }
        prodi_id: { type: integer, nullable: true }
        kohort_id: { type: integer }
        tgl_mulai: { type: string, format: date }
        tgl_selesai: { type: string, format: date }

    QuestionStoreRequest:
      type: object
      required: [question_text, type, order_num]
      properties:
        question_text: { type: string }
        type: { type: string, enum: [short_answer, multiple_choice, long_answer, dropdown, checkbox, rating, date, number] }
        options: { type: object }
        is_required: { type: boolean, default: true }
        order_num: { type: integer }

    LogicJumpRequest:
      type: object
      required: [question_id, answer_value, target_question_id]
      properties:
        question_id: { type: integer }
        answer_value: { type: string }
        target_question_id: { type: integer }
        condition_type: { type: string, enum: [equals, contains, greater_than, less_than], default: equals }

    SurveyResponseRequest:
      type: object
      required: [answers]
      properties:
        answers:
          type: array
          items:
            type: object
            properties:
              question_id: { type: integer }
              answer_value: { type: string }

    PaginationMeta:
      type: object
      properties:
        current_page: { type: integer }
        last_page: { type: integer }
        per_page: { type: integer }
        total: { type: integer }

  securitySchemes:
    apiKeyAuth:
      type: apiKey
      in: header
      name: X-API-Key
```

### 6.5 Sequence Diagram

#### 6.5.1 Sinkronisasi PDDikti NeoFeeder (CI3 + Cron)

```
┌─────────┐    ┌──────────────┐    ┌─────────────┐    ┌──────────┐    ┌─────────┐
│  Admin  │    │  Cron Job    │    │ SyncService │    │  PDDikti │    │  Queue  │
│         │    │ (CLI CI3)    │    │  (Library)  │    │          │    │(DB Table)│
└────┬────┘    └──────┬───────┘    └──────┬──────┘    └────┬─────┘    └────┬────┘
     │                │                   │                │               │
     │ [Manual Trigger│                   │                │               │
     │  via Web]      │                   │                │               │
     │ ──────────────►│                   │                │               │
     │                │ [Daily 02:00]     │                │               │
     │                │ ─────────────────►│                │               │
     │                │                   │                │               │
     │                │                   │ GET /ws/live2.php│               │
     │                │                   │ ───────────────►│               │
     │                │                   │                │               │
     │                │                   │ Response: JSON  │               │
     │                │                   │ ◄───────────────│               │
     │                │                   │                │               │
     │                │                   │ [Validate & Map]│               │
     │                │                   │ [NIM exists?]   │               │
     │                │                   │ [BR-ALM-009]    │               │
     │                │                   │                │               │
     │                │                   │ Insert to DB    │               │
     │                │                   │ Queue (jobs)    │               │
     │                │                   │ ───────────────────────────────►│
     │                │                   │                │               │
     │                │                   │                │               │ Process
     │                │                   │                │               │ via Cron
     │                │                   │                │               │
     │                │                   │ [Progress Update]│              │
     │                │                   │ ◄───────────────────────────────│
     │                │                   │                │               │
     │                │                   │ [If failed]    │               │
     │                │                   │ Retry 3x via Cron│               │
     │                │                   │ [ERR-ALM-002]  │               │
     │                │                   │                │               │
     │                │                   │ [Notify Admin]   │               │
     │                │                   │ [BR-NOT-003]     │               │
     │                │                   │                │               │
     │ [Notification] │                   │                │               │
     │ ◄─────────────│                   │                │               │
     │                │                   │                │               │
```

#### 6.5.2 Perhitungan IKU-1 (CI3 + Cron)

```
┌──────────┐    ┌──────────────┐    ┌─────────────────┐    ┌──────────┐    ┌─────────────┐
│ Cron Job │    │ IkuCalculator│    │ AlumniRepository│    │   UMP    │    │  Database   │
│(CLI CI3) │    │  (Library)   │    │    (Model)      │    │ (Model)  │    │   MySQL     │
└────┬─────┘    └──────┬───────┘    └────────┬────────┘    └────┬─────┘    └──────┬──────┘
     │                 │                     │                  │               │
     │ [Daily 02:00]   │                     │                  │               │
     │ ────────────────►│                     │                  │               │
     │                 │                     │                  │               │
     │                 │ [Get Active Kohorts]│                  │               │
     │                 │ ───────────────────►│                  │               │
     │                 │                     │                  │               │
     │                 │ [Kohort List]       │                  │               │
     │                 │ ◄───────────────────│                  │               │
     │                 │                     │                  │               │
     │                 │ [For each kohort:]  │                  │               │
     │                 │                     │                  │               │
     │                 │ [Get Alumni + Status]│                 │               │
     │                 │ ───────────────────►│                  │               │
     │                 │                     │                  │               │
     │                 │ [Alumni Data]       │                  │               │
     │                 │ ◄───────────────────│                  │               │
     │                 │                     │                  │               │
     │                 │ [Get UMP by Provinsi]│                  │               │
     │                 │ ───────────────────────────────────────►│               │
     │                 │                     │                  │               │
     │                 │ [UMP Data]          │                  │               │
     │                 │ ◄───────────────────────────────────────│               │
     │                 │                     │                  │               │
     │                 │ [Calculate Weight]  │                  │               │
     │                 │ [BR-IKU-002: gaji_aktual > gaji_range]  │               │
     │                 │ [BR-IKU-007: UMP provinsi]              │               │
     │                 │ [BR-SUR-008: bekerja > studi]           │               │
     │                 │                     │                  │               │
     │                 │ [Check Minimum]     │                  │               │
     │                 │ [BR-IKU-001]        │                  │               │
     │                 │                     │                  │               │
     │                 │ [Check V&V Rate]    │                  │               │
     │                 │ [BR-IKU-003]        │                  │               │
     │                 │                     │                  │               │
     │                 │ [Save Calculation]  │                  │               │
     │                 │ ───────────────────────────────────────────────────────►│
     │                 │                     │                  │               │
     │                 │ [Update Kohort Status]                  │               │
     │                 │ ───────────────────────────────────────────────────────►│
     │                 │                     │                  │               │
     │                 │ [If response rate < min]                  │               │
     │                 │ [Alert Admin]       │                  │               │
     │                 │                     │                  │               │
     │ [Log Complete]  │                     │                  │               │
     │ ◄──────────────│                     │                  │               │
     │                 │                     │                  │               │
```

#### 6.5.3 Alumni Mengisi Survey (PWA Offline Support - jQuery + localStorage)

```
┌──────────┐    ┌────────────┐    ┌─────────────┐    ┌──────────────┐    ┌──────────┐
│  Alumni  │    │  Browser   │    │ localStorage│    │  Backend API │    │ DB Queue │
│          │    │  (jQuery)  │    │ + IndexedDB │    │   (CI3)      │    │(CI3 Cron)│
└────┬─────┘    └─────┬──────┘    └──────┬──────┘    └──────┬───────┘    └────┬─────┘
     │                │                    │                │               │
     │ [Open Survey]  │                    │                │               │
     │ ──────────────►│                    │                │               │
     │                │ [Fetch Questions]  │                │               │
     │                │ ────────────────►  │                │               │
     │                │                    │                │               │
     │                │ [Cache Questions]  │                │               │
     │                │ ◄───────────────   │                │               │
     │                │                    │                │               │
     │ [Answer Q1]    │                    │                │               │
     │ ──────────────►│                    │                │               │
     │                │ [Auto-save]        │                │               │
     │                │ [localStorage]     │                │               │
     │                │                    │                │               │
     │ [Answer Q2]    │                    │                │               │
     │ ──────────────►│                    │                │               │
     │                │ [Auto-save]        │                │               │
     │                │                    │                │               │
     │ [Connection Lost]│                   │                │               │
     │                │                    │                │               │
     │ [Continue]     │                    │                │               │
     │ ──────────────►│                    │                │               │
     │                │ [Save to localStorage]              │               │
     │                │                    │                │               │
     │ [Connection Back]│                  │                │               │
     │                │ [Sync Event]       │                │               │
     │                │ ────────────────►  │                │               │
     │                │                    │ [POST /api/    │               │
     │                │                    │ surveys/{id}/   │               │
     │                │                    │ responses]      │               │
     │                │                    │ ───────────────►│               │
     │                │                    │                │               │
     │                │                    │ [Validate]     │               │
     │                │                    │ [BR-ALM-001]  │               │
     │                │                    │ [BR-ALM-007]  │               │
     │                │                    │                │               │
     │                │                    │ [Queue:        │               │
     │                │                    │ RecalculateIku] │               │
     │                │                    │ ─────────────────────────────►│
     │                │                    │                │               │
     │                │                    │ [Response 201]  │               │
     │                │                    │ ◄──────────────│               │
     │                │                    │                │               │
     │                │ [Clear local]      │                │               │
     │                │ ◄───────────────   │                │               │
     │                │                    │                │               │
     │ [Success Alert]│                    │                │               │
     │ ◄─────────────│                    │                │               │
     │                │                    │                │               │
```

#### 6.5.4 Notifikasi Reminder (CI3 Cron + Database Queue)

```
┌──────────┐    ┌──────────────┐    ┌─────────────────┐    ┌──────────┐    ┌──────────┐
│ Cron Job │    │ ReminderJob  │    │ AlumniRepository│    │ WhatsApp │    │  Email   │
│(CLI CI3) │    │ (Controller) │    │    (Model)      │    │  API     │    │ (CI3)    │
└────┬─────┘    └──────┬───────┘    └────────┬────────┘    └────┬─────┘    └────┬─────┘
     │                 │                     │                  │               │
     │ [Daily 08:00]   │                     │                  │               │
     │ ───────────────►│                     │                  │               │
     │                 │                     │                  │               │
     │                 │ [Get alumni: H-7,   │                  │               │
     │                 │ H-3, H-1 before     │                  │               │
     │                 │ tgl_selesai]        │                  │               │
     │                 │ [BR-NOT-001]        │                  │               │
     │                 │ ───────────────────►│                  │               │
     │                 │                     │                  │               │
     │                 │ [Alumni List]       │                  │               │
     │                 │ ◄───────────────────│                  │               │
     │                 │                     │                  │               │
     │                 │ [For each alumni:]  │                  │               │
     │                 │                     │                  │               │
     │                 │ [Send WhatsApp]     │                  │               │
     │                 │ ────────────────────────────────────────►│               │
     │                 │                     │                  │               │
     │                 │ [If WhatsApp fails] │                  │               │
     │                 │ [BR-NOT-002]        │                  │               │
     │                 │ [Retry 3x]          │                  │               │
     │                 │                     │                  │               │
     │                 │ [If still fails]    │                  │               │
     │                 │ [Send Email]        │                  │               │
     │                 │ ───────────────────────────────────────────────────────►│
     │                 │                     │                  │               │
     │                 │ [Log status]        │                  │               │
     │                 │                     │                  │               │
     │ [Log Complete]  │                     │                  │               │
     │ ◄──────────────│                     │                  │               │
     │                 │                     │                  │               │
```

### 6.6 Data Flow Diagram (DFD)

#### 6.6.1 DFD Level 0 (Context Diagram)

```
┌─────────────────────────────────────────┐
│         SISTEM TRACER STUDY v3.1       │
│            (CodeIgniter 3)             │
│                                        │
┌──────────────┐  │  ┌─────────────┐  ┌───────────────┐  │  ┌──────────────┐
│   ALUMNI     │◄─┼─►│  MANAJEMEN  │  │  PELAPORAN   │◄─┼─►│   BAN-PT     │
│ (Responden)  │  │  │   SURVEY    │  │  & ANALISIS  │  │  │  (Auditor)   │
└──────────────┘  │  └─────────────┘  └───────────────┘  │  └──────────────┘
                  │                                        │
┌──────────────┐  │  ┌─────────────┐  ┌───────────────┐  │  ┌──────────────┐
│    ADMIN     │◄─┼─►│  MANAJEMEN  │  │    IKU-1      │◄─┼─►│   BELMAWA    │
│  (Operator)  │  │  │   ALUMNI    │  │  CALCULATION  │  │  │ (Kemendikbud)│
└──────────────┘  │  └─────────────┘  └───────────────┘  │  └──────────────┘
                  │                                        │
┌──────────────┐  │  ┌─────────────┐  ┌───────────────┐  │  ┌──────────────┐
│ STAKEHOLDER  │◄─┼─►│  MANAJEMEN  │  │   KURIKULUM   │◄─┼─►│   SISTER     │
│  (Employer)  │  │  │  KURIKULUM  │  │  EVALUATION   │  │  │  (PDDikti)   │
└──────────────┘  │  └─────────────┘  └───────────────┘  │  └──────────────┘
                  │                                        │
                  └─────────────────────────────────────────┘
                                       │
                                       ▼
                            ┌─────────────────────┐
                            │  PDDikti NeoFeeder  │
                            │    (Data Source)    │
                            └─────────────────────┘
```

#### 6.6.2 DFD Level 1 (Decomposition)

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                           SISTEM TRACER STUDY v3.1 (CI3)                                   │
│                                                                                            │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐         │
│  │   PROSES 1.0    │  │   PROSES 2.0    │  │   PROSES 3.0    │  │   PROSES 4.0    │         │
│  │   MANAJEMEN     │  │   MANAJEMEN     │  │   SURVEY        │  │  IKU-1 &        │         │
│  │     ALUMNI      │  │     KOHORT      │  │   EXECUTION     │  │  PELAPORAN      │         │
│  └────────┬────────┘  └────────┬────────┘  └────────┬────────┘  └────────┬────────┘         │
│           │                  │                  │                  │                      │
│           │                  │                  │                  │                      │
│  ┌────────┴────────┐  ┌────────┴────────┐  ┌────────┴────────┐  ┌────────┴────────┐         │
│  │ D1: alumni      │  │ D2: kohorts     │  │ D3: surveys     │  │ D4: iku_calcs   │         │
│  │ D1.1: users     │  │                 │  │ D3.1: questions │  │                 │         │
│  │ D1.2: verifikasi│  │                 │  │ D3.2: responses │  │                 │         │
│  └─────────────────┘  └─────────────────┘  │ D3.3: answers   │  └─────────────────┘         │
│                                            └─────────────────┘                              │
│                                                                                            │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐         │
│  │   PROSES 5.0    │  │   PROSES 6.0    │  │   PROSES 7.0    │  │   PROSES 8.0    │         │
│  │  SURVEY BUILDER │  │   STAKEHOLDER   │  │   KURIKULUM     │  │  NOTIFIKASI     │         │
│  │                 │  │    SURVEY       │  │   MANAGEMENT    │  │  & REMINDER     │         │
│  └────────┬────────┘  └────────┬────────┘  └────────┬────────┘  └────────┬────────┘         │
│           │                  │                  │                  │                      │
│  ┌────────┴────────┐  ┌────────┴────────┐  ┌────────┴────────┐  ┌────────┴────────┐         │
│  │ D3: surveys     │  │ D5: stakeholders│  │ D6: kurikulum   │  │ D7: notifications│        │
│  │ D3.1: questions │  │ D5.1: stk_surveys│  │ D6.1: cpl       │  │                 │         │
│  │ D3.4: logic     │  │                 │  │                 │  │                 │         │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘  └─────────────────┘         │
│                                                                                            │
│  ┌─────────────────┐                                                                        │
│  │   PROSES 9.0    │                                                                        │
│  │  AUDIT TRAIL    │                                                                        │
│  │  & SECURITY     │                                                                        │
│  └────────┬────────┘                                                                        │
│           │                                                                                │
│  ┌────────┴────────┐                                                                        │
│  │ D8: activity_logs│                                                                       │
│  │ D8.1: audit_trail│                                                                       │
│  └─────────────────┘                                                                        │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

DATA FLOWS LEVEL 1:
─────────────────────────────────────────────────────────────────────────────────────────────
1.  ALUMNI ──► PROSES 1.0: Data registrasi, update profil, isi survey
2.  ADMIN ──► PROSES 1.0: Import Excel, verifikasi data, sync PDDikti
3.  PDDikti ──► PROSES 1.0: Data mahasiswa lulus (NIM, Nama, Prodi, Yudisium)
4.  PROSES 1.0 ──► PROSES 2.0: Assign alumni ke kohort
5.  PROSES 2.0 ──► PROSES 3.0: Aktifkan survey untuk kohort
6.  ALUMNI ──► PROSES 3.0: Jawaban survey, auto-save (localStorage)
7.  PROSES 3.0 ──► PROSES 4.0: Trigger recalculation IKU-1
8.  PROSES 4.0 ──► BELMAWA: Export template Excel
9.  ADMIN ──► PROSES 4.0: Verifikasi data, approve IKU
10. STAKEHOLDER ──► PROSES 6.0: Penilaian kompetensi alumni
11. PROSES 6.0 ──► PROSES 7.0: Gap analysis CPL
12. PROSES 8.0 ──► ALUMNI: Reminder H-7, H-3, H-1 (via Cron)
13. SEMUA PROSES ──► PROSES 9.0: Log aktivitas (immutable)
```

---

## 7. KEBUTUHAN DATA, DATABASE & DATA DICTIONARY

### 7.1 Skema Tabel Detail (Lengkap)

```sql
-- Tabel: users (Autentikasi)
CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('super_admin','admin_pusat_karir','admin_prodi','dosen','alumni','stakeholder','reviewer') DEFAULT 'alumni',
  prodi_id INT NULL,
  nama_lengkap VARCHAR(100) NOT NULL,
  no_hp VARCHAR(15),
  avatar VARCHAR(255),
  status ENUM('aktif','nonaktif','pending') DEFAULT 'pending',
  email_verified_at TIMESTAMP NULL,
  two_factor_enabled BOOLEAN DEFAULT FALSE,
  two_factor_secret VARCHAR(32) NULL,
  last_login DATETIME,
  last_login_ip VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_role (role),
  INDEX idx_prodi (prodi_id),
  INDEX idx_email_verified (email_verified_at)
) ENGINE=InnoDB;

-- Tabel: kohorts (Manajemen Kohort)
CREATE TABLE kohorts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tahun_lulus YEAR NOT NULL,
  prodi_id INT NOT NULL,
  target_populasi INT NOT NULL DEFAULT 0,
  min_responden INT NOT NULL DEFAULT 0,
  jumlah_responden INT NOT NULL DEFAULT 0,
  response_rate DECIMAL(5,2) DEFAULT 0.00,
  status ENUM('aktif','nonaktif','selesai') DEFAULT 'aktif',
  tgl_mulai DATE,
  tgl_selesai DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_kohort (tahun_lulus, prodi_id),
  FOREIGN KEY (prodi_id) REFERENCES program_studi(id),
  INDEX idx_status (status),
  INDEX idx_tahun (tahun_lulus)
) ENGINE=InnoDB;

-- Tabel: alumni
CREATE TABLE alumni (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED UNIQUE NULL,
  nim VARCHAR(20) UNIQUE NOT NULL,
  nama VARCHAR(100) NOT NULL,
  prodi_id INT NOT NULL,
  kohort_id BIGINT UNSIGNED NOT NULL,
  tahun_lulus YEAR NOT NULL,
  email VARCHAR(100),
  no_hp VARCHAR(15),
  alamat TEXT,
  kota VARCHAR(50),
  provinsi VARCHAR(50),
  status_kerja ENUM('bekerja','wirausaha','lanjut_studi','belum_bekerja') DEFAULT 'belum_bekerja',
  nama_perusahaan VARCHAR(100),
  bidang_pekerjaan VARCHAR(50),
  jabatan VARCHAR(50),
  gaji_range ENUM('<3jt','3-5jt','5-10jt','10-20jt','>20jt'),
  gaji_aktual DECIMAL(12,2) NULL,
  kesesuaian_bidang ENUM('sesuai','tidak_sesuai','kurang_sesuai'),
  masa_tunggu_bulan INT DEFAULT 0,
  tanggal_mulai_kerja DATE NULL,
  tanggal_yudisium DATE NOT NULL,
  verifikasi_status ENUM('pending','terverifikasi','ditolak') DEFAULT 'pending',
  verifikasi_by BIGINT UNSIGNED NULL,
  verifikasi_at TIMESTAMP NULL,
  verifikasi_keterangan TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (prodi_id) REFERENCES program_studi(id),
  FOREIGN KEY (kohort_id) REFERENCES kohorts(id),
  FOREIGN KEY (verifikasi_by) REFERENCES users(id),
  INDEX idx_nim (nim),
  INDEX idx_status_kerja (status_kerja),
  INDEX idx_verifikasi (verifikasi_status),
  INDEX idx_kohort (kohort_id),
  INDEX idx_prodi_tahun (prodi_id, tahun_lulus)
) ENGINE=InnoDB;

-- Tabel: surveys
CREATE TABLE surveys (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(200) NOT NULL,
  slug VARCHAR(200) UNIQUE,
  deskripsi TEXT,
  prodi_id INT,
  kohort_id BIGINT UNSIGNED,
  tipe ENUM('alumni','stakeholder','internal') DEFAULT 'alumni',
  tgl_mulai DATE,
  tgl_selesai DATE,
  status ENUM('draft','published','active','closed','archived') DEFAULT 'draft',
  created_by BIGINT UNSIGNED,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (prodi_id) REFERENCES program_studi(id),
  FOREIGN KEY (kohort_id) REFERENCES kohorts(id),
  FOREIGN KEY (created_by) REFERENCES users(id),
  INDEX idx_status (status),
  INDEX idx_kohort (kohort_id),
  INDEX idx_prodi (prodi_id)
) ENGINE=InnoDB;

-- Tabel: survey_sections
CREATE TABLE survey_sections (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  survey_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(100),
  description TEXT,
  order_num INT DEFAULT 0,
  FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
  INDEX idx_survey_order (survey_id, order_num)
) ENGINE=InnoDB;

-- Tabel: survey_questions
CREATE TABLE survey_questions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  survey_id BIGINT UNSIGNED NOT NULL,
  section_id BIGINT UNSIGNED NULL,
  question_text TEXT NOT NULL,
  type ENUM('short_answer','multiple_choice','long_answer','dropdown','checkbox','rating','date','number') NOT NULL,
  options JSON,
  is_required BOOLEAN DEFAULT TRUE,
  is_belma_inti BOOLEAN DEFAULT FALSE,
  belma_kode VARCHAR(20) NULL,
  order_num INT DEFAULT 0,
  validation_rules JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
  FOREIGN KEY (section_id) REFERENCES survey_sections(id) ON DELETE SET NULL,
  INDEX idx_survey_order (survey_id, order_num),
  INDEX idx_belma (is_belma_inti),
  INDEX idx_type (type)
) ENGINE=InnoDB;

-- Tabel: survey_logic (Logic Jump)
CREATE TABLE survey_logic (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  survey_id BIGINT UNSIGNED NOT NULL,
  question_id BIGINT UNSIGNED NOT NULL,
  answer_value VARCHAR(255) NOT NULL,
  target_question_id BIGINT UNSIGNED NOT NULL,
  condition_type ENUM('equals','contains','greater_than','less_than') DEFAULT 'equals',
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
  FOREIGN KEY (question_id) REFERENCES survey_questions(id) ON DELETE CASCADE,
  FOREIGN KEY (target_question_id) REFERENCES survey_questions(id) ON DELETE CASCADE,
  CHECK (question_id != target_question_id),
  INDEX idx_survey (survey_id),
  INDEX idx_question (question_id)
) ENGINE=InnoDB;

-- Tabel: survey_responses
CREATE TABLE survey_responses (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  survey_id BIGINT UNSIGNED NOT NULL,
  alumni_id BIGINT UNSIGNED NULL,
  stakeholder_id BIGINT UNSIGNED NULL,
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(45),
  user_agent TEXT,
  completion_time INT,
  status ENUM('in_progress','completed','abandoned') DEFAULT 'in_progress',
  FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
  FOREIGN KEY (alumni_id) REFERENCES alumni(id) ON DELETE CASCADE,
  UNIQUE KEY unique_response (survey_id, alumni_id),
  INDEX idx_alumni (alumni_id),
  INDEX idx_status (status),
  INDEX idx_submitted (submitted_at)
) ENGINE=InnoDB;

-- Tabel: survey_answers
CREATE TABLE survey_answers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  response_id BIGINT UNSIGNED NOT NULL,
  question_id BIGINT UNSIGNED NOT NULL,
  answer_value TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (response_id) REFERENCES survey_responses(id) ON DELETE CASCADE,
  FOREIGN KEY (question_id) REFERENCES survey_questions(id) ON DELETE CASCADE,
  INDEX idx_response (response_id),
  INDEX idx_question (question_id)
) ENGINE=InnoDB;

-- Tabel: stakeholders (Pengguna Lulusan)
CREATE TABLE stakeholders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED UNIQUE NULL,
  nama_institusi VARCHAR(100) NOT NULL,
  bidang_industri VARCHAR(50),
  nama_pic VARCHAR(100),
  email VARCHAR(100),
  no_hp VARCHAR(15),
  alamat TEXT,
  skala_usaha ENUM('mikro','kecil','menengah','besar'),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_industri (bidang_industri)
) ENGINE=InnoDB;

-- Tabel: stakeholder_surveys
CREATE TABLE stakeholder_surveys (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  stakeholder_id BIGINT UNSIGNED NOT NULL,
  alumni_id BIGINT UNSIGNED NOT NULL,
  survey_id BIGINT UNSIGNED NOT NULL,
  rating_kompetensi JSON,
  kesesuaian_cpl ENUM('sangat_sesuai','sesuai','kurang','tidak_sesuai'),
  rekomendasi TEXT,
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (stakeholder_id) REFERENCES stakeholders(id),
  FOREIGN KEY (alumni_id) REFERENCES alumni(id),
  FOREIGN KEY (survey_id) REFERENCES surveys(id),
  INDEX idx_stakeholder (stakeholder_id),
  INDEX idx_alumni (alumni_id)
) ENGINE=InnoDB;

-- Tabel: kurikulum
CREATE TABLE kurikulum (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  prodi_id INT NOT NULL,
  kode_matakuliah VARCHAR(20) NOT NULL,
  nama_matakuliah VARCHAR(100) NOT NULL,
  sks INT NOT NULL,
  semester INT NOT NULL,
  cpl_mapping JSON,
  tahun_kurikulum YEAR NOT NULL,
  status ENUM('aktif','nonaktif') DEFAULT 'aktif',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (prodi_id) REFERENCES program_studi(id),
  INDEX idx_prodi (prodi_id),
  INDEX idx_tahun (tahun_kurikulum)
) ENGINE=InnoDB;

-- Tabel: cpl (Capaian Pembelajaran Lulusan)
CREATE TABLE cpl (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  prodi_id INT NOT NULL,
  kode_cpl VARCHAR(20) NOT NULL,
  deskripsi TEXT NOT NULL,
  sn_dikti_ref VARCHAR(50),
  kkni_ref VARCHAR(50),
  profil_lulusan_ref VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (prodi_id) REFERENCES program_studi(id),
  INDEX idx_prodi (prodi_id),
  INDEX idx_kode (kode_cpl)
) ENGINE=InnoDB;

-- Tabel: iku_calculations
CREATE TABLE iku_calculations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tahun_iku YEAR NOT NULL,
  prodi_id INT NOT NULL,
  kohort_id BIGINT UNSIGNED NOT NULL,
  total_lulusan INT NOT NULL DEFAULT 0,
  total_responden INT NOT NULL DEFAULT 0,
  valid_responden INT NOT NULL DEFAULT 0,
  response_rate DECIMAL(5,2) DEFAULT 0.00,
  responden_bekerja INT DEFAULT 0,
  responden_wirausaha INT DEFAULT 0,
  responden_studi INT DEFAULT 0,
  responden_belum_kerja INT DEFAULT 0,
  bobot_total DECIMAL(8,2) DEFAULT 0.00,
  bobot_avg DECIMAL(5,2) DEFAULT 0.00,
  iku_score DECIMAL(5,2) DEFAULT 0.00,
  verifikasi_rate DECIMAL(5,2) DEFAULT 0.00,
  status_minimum ENUM('tercapai','belum_tercapai') DEFAULT 'belum_tercapai',
  status_iku ENUM('draft','calculated','reviewed','terverifikasi','dikirim') DEFAULT 'draft',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (prodi_id) REFERENCES program_studi(id),
  FOREIGN KEY (kohort_id) REFERENCES kohorts(id),
  UNIQUE KEY unique_iku (tahun_iku, prodi_id, kohort_id),
  INDEX idx_status (status_iku),
  INDEX idx_tahun (tahun_iku)
) ENGINE=InnoDB;

-- Tabel: verifikasi_data
CREATE TABLE verifikasi_data (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  alumni_id BIGINT UNSIGNED NOT NULL,
  verifikasi_by BIGINT UNSIGNED NOT NULL,
  status ENUM('pending','terverifikasi','ditolak') DEFAULT 'pending',
  keterangan TEXT,
  bukti_file VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (alumni_id) REFERENCES alumni(id),
  FOREIGN KEY (verifikasi_by) REFERENCES users(id),
  INDEX idx_alumni (alumni_id),
  INDEX idx_status (status)
) ENGINE=InnoDB;

-- Tabel: activity_logs (Audit Trail - Immutable)
CREATE TABLE activity_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED,
  action VARCHAR(50) NOT NULL,
  module VARCHAR(50) NOT NULL,
  description TEXT,
  ip_address VARCHAR(45),
  user_agent VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  INDEX idx_module (module),
  INDEX idx_action (action),
  INDEX idx_created (created_at),
  INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- Tabel: notifications
CREATE TABLE notifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  tipe ENUM('email','whatsapp','telegram','push') NOT NULL,
  judul VARCHAR(100),
  pesan TEXT,
  status ENUM('pending','terkirim','gagal') DEFAULT 'pending',
  retry_count INT DEFAULT 0,
  sent_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  INDEX idx_user_status (user_id, status),
  INDEX idx_tipe (tipe)
) ENGINE=InnoDB;

-- Tabel: ump_provinsi (Upah Minimum Provinsi)
CREATE TABLE ump_provinsi (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  provinsi VARCHAR(50) NOT NULL,
  tahun YEAR NOT NULL,
  nilai_ump DECIMAL(12,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_ump (provinsi, tahun),
  INDEX idx_provinsi (provinsi),
  INDEX idx_tahun (tahun)
) ENGINE=InnoDB;

-- Tabel: api_keys
CREATE TABLE api_keys (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(100),
  key_hash VARCHAR(255) NOT NULL,
  abilities JSON,
  last_used_at TIMESTAMP NULL,
  expires_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  INDEX idx_key (key_hash),
  INDEX idx_expires (expires_at)
) ENGINE=InnoDB;

-- Tabel: jobs (Queue untuk background processing CI3)
CREATE TABLE jobs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  queue VARCHAR(50) NOT NULL DEFAULT 'default',
  payload TEXT NOT NULL,
  attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  reserved_at TIMESTAMP NULL,
  available_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_queue_reserved (queue, reserved_at),
  INDEX idx_available_at (available_at)
) ENGINE=InnoDB;

-- Tabel: failed_jobs
CREATE TABLE failed_jobs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  connection TEXT NOT NULL,
  queue TEXT NOT NULL,
  payload TEXT NOT NULL,
  exception TEXT NOT NULL,
  failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel: login_attempts (Rate Limiting CI3)
CREATE TABLE login_attempts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ip_address VARCHAR(45) NOT NULL,
  username VARCHAR(50),
  attempt_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  success BOOLEAN DEFAULT FALSE,
  INDEX idx_ip (ip_address),
  INDEX idx_attempt_at (attempt_at)
) ENGINE=InnoDB;

-- Tabel: cache (CI3 File Cache Metadata - Optional)
CREATE TABLE cache (
  id VARCHAR(255) NOT NULL PRIMARY KEY,
  data BLOB NOT NULL,
  expire INT NOT NULL,
  INDEX idx_expire (expire)
) ENGINE=InnoDB;
```

### 7.2 Data Dictionary Lengkap

| Tabel | Kolom | Tipe | Nullable | Default | Constraint | Deskripsi untuk Programmer |
|-------|-------|------|----------|---------|------------|---------------------------|
| **users** | id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK | ID unik user |
| | username | VARCHAR(50) | NO | - | UNIQUE | Username login |
| | email | VARCHAR(100) | NO | - | UNIQUE, format email | Email login & notifikasi |
| | password_hash | VARCHAR(255) | NO | - | - | Hash bcrypt/Argon2. Jangan pernah simpan plain text |
| | role | ENUM | NO | 'alumni' | - | Role menentukan hak akses. Gunakan custom library auth |
| | prodi_id | INT | YES | NULL | FK → program_studi | NULL untuk Super Admin |
| | nama_lengkap | VARCHAR(100) | NO | - | - | Nama display |
| | no_hp | VARCHAR(15) | YES | NULL | - | **PII - Enkripsi AES-256** |
| | avatar | VARCHAR(255) | YES | NULL | - | Path file avatar |
| | status | ENUM | NO | 'pending' | - | pending = belum verifikasi email |
| | email_verified_at | TIMESTAMP | YES | NULL | - | NULL = belum verifikasi. Cek BR-ALM-005 |
| | two_factor_enabled | BOOLEAN | NO | FALSE | - | Aktif jika 2FA di-enable |
| | two_factor_secret | VARCHAR(32) | YES | NULL | - | Secret key TOTP |
| | last_login | DATETIME | YES | NULL | - | Untuk audit & session management |
| | last_login_ip | VARCHAR(45) | YES | NULL | - | IP terakhir login |
| | created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - | Auto oleh CI3 |
| | updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | Auto update | Auto oleh CI3 |
| **kohorts** | id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK | ID kohort |
| | tahun_lulus | YEAR | NO | - | - | Tahun yudisium kelompok |
| | prodi_id | INT | NO | - | FK, UNIQUE(tahun_lulus, prodi_id) | Kombinasi unik |
| | target_populasi | INT | NO | 0 | CHECK ≥ 0 | Total lulusan dari PDDikti |
| | min_responden | INT | NO | 0 | CHECK ≥ 0 | Dihitung otomatis dari threshold |
| | jumlah_responden | INT | NO | 0 | CHECK ≥ 0 | Counter real-time |
| | response_rate | DECIMAL(5,2) | NO | 0.00 | CHECK 0-100 | (jumlah_responden / target_populasi) * 100 |
| | status | ENUM | NO | 'aktif' | - | aktif = bisa isi survey |
| | tgl_mulai | DATE | YES | NULL | - | Buka survey |
| | tgl_selesai | DATE | YES | NULL | - | Tutup survey |
| **alumni** | id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK | - |
| | user_id | BIGINT UNSIGNED | YES | NULL | FK, UNIQUE | NULL jika import bulk belum claim |
| | nim | VARCHAR(20) | NO | - | UNIQUE, INDEX | **Immutable** setelah create |
| | nama | VARCHAR(100) | NO | - | - | - |
| | prodi_id | INT | NO | - | FK | - |
| | kohort_id | BIGINT UNSIGNED | NO | - | FK, INDEX | Auto-assign dari tahun_lulus |
| | tahun_lulus | YEAR | NO | - | - | - |
| | email | VARCHAR(100) | YES | NULL | - | - |
| | no_hp | VARCHAR(15) | YES | NULL | - | **PII - Enkripsi** |
| | alamat | TEXT | YES | NULL | - | **PII - Enkripsi** |
| | kota | VARCHAR(50) | YES | NULL | - | - |
| | provinsi | VARCHAR(50) | YES | NULL | - | Untuk lookup UMP |
| | status_kerja | ENUM | NO | 'belum_bekerja' | - | - |
| | nama_perusahaan | VARCHAR(100) | YES | NULL | - | - |
| | bidang_pekerjaan | VARCHAR(50) | YES | NULL | - | - |
| | jabatan | VARCHAR(50) | YES | NULL | - | - |
| | gaji_range | ENUM | YES | NULL | - | Untuk display |
| | gaji_aktual | DECIMAL(12,2) | YES | NULL | - | **PII - Enkripsi**. Sumber kebenaran untuk IKU |
| | kesesuaian_bidang | ENUM | YES | NULL | - | - |
| | masa_tunggu_bulan | INT | NO | 0 | CHECK ≥ 0 | Auto-calculate |
| | tanggal_mulai_kerja | DATE | YES | NULL | - | Untuk hitung masa tunggu |
| | tanggal_yudisium | DATE | NO | - | - | Sumber dari PDDikti |
| | verifikasi_status | ENUM | NO | 'pending' | INDEX | terverifikasi = masuk IKU |
| | verifikasi_by | BIGINT UNSIGNED | YES | NULL | FK → users | Admin yang verifikasi |
| | verifikasi_at | TIMESTAMP | YES | NULL | - | Waktu verifikasi |
| | verifikasi_keterangan | TEXT | YES | NULL | - | Alasan jika ditolak |
| **surveys** | id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK | - |
| | judul | VARCHAR(200) | NO | - | - | - |
| | slug | VARCHAR(200) | YES | NULL | UNIQUE | Untuk URL friendly |
| | deskripsi | TEXT | YES | NULL | - | - |
| | prodi_id | INT | YES | NULL | FK | NULL = survey PT-wide |
| | kohort_id | BIGINT UNSIGNED | YES | NULL | FK | - |
| | tipe | ENUM | NO | 'alumni' | - | Menentukan flow |
| | tgl_mulai | DATE | YES | NULL | - | - |
| | tgl_selesai | DATE | YES | NULL | - | - |
| | status | ENUM | NO | 'draft' | INDEX | State machine: lihat bagian 3.10 |
| | created_by | BIGINT UNSIGNED | YES | NULL | FK → users | - |
| **survey_questions** | id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK | - |
| | survey_id | BIGINT UNSIGNED | NO | - | FK, CASCADE DELETE | - |
| | section_id | BIGINT UNSIGNED | YES | NULL | FK, SET NULL | - |
| | question_text | TEXT | NO | - | - | - |
| | type | ENUM | NO | - | - | Validasi tipe input |
| | options | JSON | YES | NULL | - | Wajib untuk multiple_choice, checkbox, dropdown |
| | is_required | BOOLEAN | NO | TRUE | - | - |
| | is_belma_inti | BOOLEAN | NO | FALSE | INDEX | TRUE = locked |
| | belma_kode | VARCHAR(20) | YES | NULL | - | Kode referensi Belmawa |
| | order_num | INT | NO | 0 | - | Urutan tampilan |
| | validation_rules | JSON | YES | NULL | - | min_length, max_length, regex |
| **survey_logic** | id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK | - |
| | survey_id | BIGINT UNSIGNED | NO | - | FK | - |
| | question_id | BIGINT UNSIGNED | NO | - | FK | Source question |
| | answer_value | VARCHAR(255) | NO | - | - | Value yang trigger jump |
| | target_question_id | BIGINT UNSIGNED | NO | - | FK | Destination question |
| | condition_type | ENUM | NO | 'equals' | - | - |
| | is_active | BOOLEAN | NO | TRUE | - | - |
| | **CHECK** | - | - | - | question_id != target_question_id | Prevent self-reference |
| **survey_responses** | id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK | - |
| | survey_id | BIGINT UNSIGNED | NO | - | FK | - |
| | alumni_id | BIGINT UNSIGNED | YES | NULL | FK | NULL untuk stakeholder survey |
| | stakeholder_id | BIGINT UNSIGNED | YES | NULL | FK | NULL untuk alumni survey |
| | submitted_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - | - |
| | ip_address | VARCHAR(45) | YES | NULL | - | Untuk audit |
| | user_agent | TEXT | YES | NULL | - | Untuk audit |
| | completion_time | INT | YES | NULL | - | Detik |
| | status | ENUM | NO | 'in_progress' | - | - |
| | **UNIQUE** | - | - | - | (survey_id, alumni_id) | BR-ALM-007 |
| **iku_calculations** | id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK | - |
| | tahun_iku | YEAR | NO | - | - | Tahun pelaporan IKU |
| | prodi_id | INT | NO | - | FK | - |
| | kohort_id | BIGINT UNSIGNED | NO | - | FK | - |
| | total_lulusan | INT | NO | 0 | - | Dari kohort.target_populasi |
| | total_responden | INT | NO | 0 | - | Total yang mengisi |
| | valid_responden | INT | NO | 0 | - | Yang lolos V&V |
| | response_rate | DECIMAL(5,2) | NO | 0.00 | - | - |
| | responden_bekerja | INT | NO | 0 | - | - |
| | responden_wirausaha | INT | NO | 0 | - | - |
| | responden_studi | INT | NO | 0 | - | - |
| | responden_belum_kerja | INT | NO | 0 | - | - |
| | bobot_total | DECIMAL(8,2) | NO | 0.00 | - | Σ bobot semua responden |
| | bobot_avg | DECIMAL(5,2) | NO | 0.00 | - | bobot_total / valid_responden |
| | iku_score | DECIMAL(5,2) | NO | 0.00 | - | Final score |
| | verifikasi_rate | DECIMAL(5,2) | NO | 0.00 | - | % terverifikasi |
| | status_minimum | ENUM | NO | 'belum_tercapai' | - | BR-IKU-001 |
| | status_iku | ENUM | NO | 'draft' | - | State machine |
| | **UNIQUE** | - | - | - | (tahun_iku, prodi_id, kohort_id) | Satu record per kombinasi |
| **activity_logs** | id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK | - |
| | user_id | BIGINT UNSIGNED | YES | NULL | FK | NULL untuk system action |
| | action | VARCHAR(50) | NO | - | INDEX | create, update, delete, login, export |
| | module | VARCHAR(50) | NO | - | INDEX | alumni, survey, iku, auth |
| | description | TEXT | YES | NULL | - | Detail JSON |
| | ip_address | VARCHAR(45) | YES | NULL | - | - |
| | user_agent | VARCHAR(255) | YES | NULL | - | - |
| | created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - | **Immutable** |
| | **NO DELETE** | - | - | - | - | BR-SEC-001: Hanya INSERT |
| **jobs** | id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK | Queue untuk background processing |
| | queue | VARCHAR(50) | NO | 'default' | - | Nama queue |
| | payload | TEXT | NO | - | - | JSON data job |
| | attempts | TINYINT UNSIGNED | NO | 0 | - | Jumlah retry |
| | reserved_at | TIMESTAMP | YES | NULL | - | Waktu diproses |
| | available_at | TIMESTAMP | NO | - | - | Waktu tersedia untuk diproses |
| | created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - | - |
| **login_attempts** | id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK | Rate limiting database-based |
| | ip_address | VARCHAR(45) | NO | - | INDEX | IP address |
| | username | VARCHAR(50) | YES | NULL | - | Username yang dicoba |
| | attempt_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - | Waktu percobaan |
| | success | BOOLEAN | NO | FALSE | - | Status berhasil/tidak |

### 7.3 Database Strategy & Caching (CI3 Native)

#### 7.3.1 Indexing Strategy (Tanpa Partitioning)

| Tabel | Index Strategy | Alasan | Implementasi |
|-------|---------------|--------|-------------|
| **survey_answers** | Composite index (response_id, question_id) | Data bisa jutaan per survey | INDEX di response_id dan question_id |
| **activity_logs** | Index (created_at, module, action) | Growth tak terbatas, query by date | INDEX di created_at, module, action |
| **survey_responses** | Index (submitted_at, status, survey_id) | Query laporan per periode | INDEX di submitted_at, status, survey_id |
| **notifications** | Index (created_at, status, user_id) | High volume, auto-purge after 1 year | INDEX di created_at, status, user_id. Cron purge bulanan. |
| **alumni** | Composite index (prodi_id, tahun_lulus, status_kerja) | Query filter utama | INDEX di prodi_id, tahun_lulus, status_kerja |
| **iku_calculations** | Index (tahun_iku, prodi_id, status_iku) | Query dashboard IKU | INDEX di tahun_iku, prodi_id, status_iku |

#### 7.3.2 Caching Strategy (CI3 File-Based Cache)

| Cache Key Pattern | TTL | Data | Invalidation Trigger |
|-------------------|-----|------|---------------------|
| iku_dashboard_{tahun}_{prodi_id} | 1 jam | IKU score, gauge data | IkuCalculator::calculate() complete |
| alumni_list_{prodi_id}_{page} | 30 menit | Paginated alumni list | Alumni create/update/delete |
| survey_questions_{survey_id} | 24 jam | Question list + logic | Survey publish/update |
| survey_progress_{alumni_id}_{survey_id} | 7 hari | Local progress (PWA) | Survey submit atau 7 hari |
| ump_{provinsi}_{tahun} | 1 tahun | UMP value | Manual update by admin |
| session_{session_id} | 30 menit | User session | Logout atau idle |
| rate_limit_{ip} | 30 menit | Login attempt count | BR-SEC-005 |
| report_pdf_{report_id} | 24 jam | Generated PDF path | - |

**Cache Warming Strategy:**
- Dashboard IKU di-cache saat cron daily run (02:00 WIB) via CI3 CLI controller
- Alumni list di-cache saat first request, invalidate on CUD (Create/Update/Delete)
- Survey questions di-preload saat survey published → active
- Gunakan `CI3 Cache Library` dengan driver `file` (default, tanpa Redis)

---

## 8. KEAMANAN SISTEM & THREAT MODEL

### 8.1 Mekanisme Keamanan

| Layer | Mekanisme | Implementasi |
|-------|-----------|-------------|
| **Input Validation** | Sanitasi semua input | CI3 Form Validation, XSS filtering, input sanitization |
| **SQL Injection** | Query Binding / ORM | CI3 Query Builder, parameterized query |
| **XSS Protection** | Output Escaping | `htmlspecialchars()`, CSP headers, HTML Purifier |
| **CSRF Protection** | Token validation | CI3 CSRF token, Security library |
| **Session Security** | Regenerate ID, HTTP-only, Secure, SameSite | CI3 Session Config (file-based atau database) |
| **File Upload** | Type & size validation, storage outside web root | CI3 Upload Library, whitelist MIME |
| **Password** | Bcrypt/Argon2 hashing | `password_hash()` PHP native, min 12 karakter |
| **API Security** | API Key custom | CI3 REST Controller + API Key database |
| **Rate Limiting** | Database-based throttle | Tabel login_attempts, max 5 per IP per menit |
| **Data Privacy** | Encryption at rest & in transit | CI3 Encryption Library (AES-256), TLS 1.3 |
| **Audit Trail** | Immutable | Log tidak dapat dihapus, hanya append |

### 8.2 RBAC (Role-Based Access Control)

```php
// Contoh: CI3 Custom Library - Auth_lib.php
class Auth_lib {

    public function check_survey_view($user, $survey) {
        return $user['role'] === 'super_admin' ||
               $user['role'] === 'admin_pusat_karir' ||
               ($user['role'] === 'admin_prodi' && $user['prodi_id'] == $survey['prodi_id']);
    }

    public function check_survey_create($user) {
        return in_array($user['role'], ['super_admin', 'admin_pusat_karir', 'admin_prodi']);
    }

    public function check_survey_delete($user, $survey) {
        // Cek apakah survey punya pertanyaan inti
        $CI =& get_instance();
        $CI->load->model('Survey_question_model');
        $has_belma = $CI->Survey_question_model->has_belma_inti($survey['id']);

        if ($has_belma) {
            return false; // Pertanyaan inti tidak dapat dihapus
        }

        return $user['role'] === 'super_admin' ||
               ($user['role'] === 'admin_prodi' && $user['prodi_id'] == $survey['prodi_id']);
    }

    public function check_survey_publish($user, $survey) {
        $CI =& get_instance();
        $CI->load->model('Survey_question_model');
        $belma_count = $CI->Survey_question_model->count_belma_inti($survey['id']);

        if ($belma_count < 20) {
            return false; // BR-SUR-002
        }

        return $this->check_survey_create($user);
    }
}
```

### 8.3 Security Threat Model (STRIDE)

| Threat | Kategori | Skenario | Mitigasi | Requirement |
|--------|----------|----------|----------|-------------|
| **Spoofing** | Authentication | Attacker login sebagai admin dengan credential curian | bcrypt/Argon2, 2FA, rate limiting | AUTH-002, AUTH-007, SEC-007 |
| **Spoofing** | Authentication | Attacker pakai API key expired/lain | API key rotation 90 hari, validasi database | SEC-008, BR-SEC-003 |
| **Tampering** | Integrity | Attacker ubah data IKU-1 hasil perhitungan | Immutable audit trail, checksum, V&V | SEC-010, IKU-005 |
| **Tampering** | Integrity | Attacker ubah pertanyaan inti Belmawa | is_belma_inti flag, policy restriction | BR-SUR-001 |
| **Repudiation** | Non-repudiation | Admin hapus data alumni lalu deny | Immutable activity log, soft delete | SEC-010, BR-SEC-001 |
| **Repudiation** | Non-repudiation | Alumni claim tidak pernah isi survey | IP logging, timestamp, digital fingerprint | SUR-AL-011 |
| **Information Disclosure** | Confidentiality | SQL Injection leak data alumni | CI3 Query Builder, parameterized query | SEC-001 |
| **Information Disclosure** | Confidentiality | XSS steal session cookie | CSP headers, HttpOnly, Secure, SameSite | SEC-002, SEC-008 |
| **Information Disclosure** | Confidentiality | Database dump expose gaji alumni | AES-256 encryption for PII, DB access control | SEC-006, SEC-009 |
| **Denial of Service** | Availability | DDoS login endpoint | Rate limiting, Cloudflare/WAF, fail2ban | SEC-007, PERF-005 |
| **Denial of Service** | Availability | Export 1 juta record crash server | Background queue (database), pagination, timeout | PERF-007, PERF-008 |
| **Elevation of Privilege** | Authorization | Admin Prodi akses data prodi lain | Custom library otorisasi, FK check | SEC-005, BR-SEC-004 |
| **Elevation of Privilege** | Authorization | Alumni akses survey builder | Role-based controller checks | AUTH-001 |

### 8.4 Data Classification & Handling

| Klasifikasi | Data | Handling | Enkripsi | Retention |
|-------------|------|----------|----------|-----------|
| **Critical** | NIM, NIK (jika ada), gaji_aktual, no_hp, alamat | AES-256 at rest, TLS 1.3 in transit, masked in log | ✅ AES-256 | 5 tahun |
| **Internal** | Nama, email, prodi, status kerja | Access control by role, log access | ❌ | 5 tahun |
| **Public** | Statistik IKU-1, grafik agregat | Bisa diakses reviewer/auditor | ❌ | 5 tahun |

---

## 9. PENGUJIAN, KUALITAS & TRACEABILITY

### 9.1 Rencana Pengujian

| Fase | Jenis Pengujian | Tools | Coverage |
|------|----------------|-------|----------|
| Unit Testing | PHPUnit standalone | PHPUnit (Composer) | Models, Libraries, Helpers (≥80%) |
| Integration Testing | API Testing | Postman / cURL scripts | Controller endpoints, API, Webhook |
| UI Testing | Manual + Automated | Selenium IDE / Manual | Form submission, navigation, logic jump |
| Security Testing | OWASP ZAP, SQLMap | ZAP | SQLi, XSS, CSRF, Auth bypass |
| Performance Testing | Load Testing | Apache JMeter / Loader.io | 1000 concurrent users |
| Compliance Testing | Manual + Automated | Custom Script (PHP) | Validasi pertanyaan inti, format IKU, template Belmawa |
| UAT | User Acceptance | Manual | End-to-end workflow oleh Pusat Karir |

### 9.2 Test Cases (Lengkap)

| ID | Modul | Skenario | Expected Result | Business Rule |
|----|-------|----------|----------------|---------------|
| TC-SUR-001 | Survey | Admin membuat survey baru | Survey tersimpan dengan status "draft", pertanyaan inti otomatis ter-generate | BR-SUR-002 |
| TC-SUR-002 | Logic Jump | Alumni pilih "Sudah Bekerja" | Sistem menampilkan pertanyaan bidang pekerjaan | SUR-AL-002 |
| TC-SUR-003 | Logic Jump | Alumni pilih "Belum Bekerja" | Sistem skip pertanyaan bidang pekerjaan, lanjut ke Q2B | SUR-AL-002 |
| TC-SUR-004 | Pertanyaan Inti | Admin mencoba hapus pertanyaan inti | Sistem menolak dengan pesan "Pertanyaan inti Belmawa tidak dapat dihapus" | BR-SUR-001 |
| TC-SUR-005 | Publish | Admin publish survey tapi pertanyaan inti < 20 | Sistem reject dengan ERR-SUR-002 | BR-SUR-002 |
| TC-ALM-001 | Alumni | Import 1000 data dari Excel | Semua data tersimpan dengan validasi NIM unik, auto-assign kohort | BR-ALM-006 |
| TC-ALM-002 | Kohort | Generate kohort 2024 | Sistem auto-assign alumni lulus 2024 ke kohort aktif | BR-ALM-001 |
| TC-ALM-003 | Masa Tunggu | Alumni input tanggal kerja sebelum yudisium | Sistem reject dengan ERR-ALM-005 | BR-ALM-004 |
| TC-IKU-001 | IKU-1 | Hitung IKU-1 kohort 2024 | Skor terhitung dengan benar berdasarkan bobot gaji & masa tunggu | BR-IKU-002 |
| TC-IKU-002 | IKU-1 | Response rate < minimum | Status "belum_tercapai", alert ke admin, export dicegah | BR-IKU-001 |
| TC-IKU-003 | Export | Export template Belmawa | File Excel sesuai format tracerstudy.kemdikbud.go.id | BR-IKU-005 |
| TC-IKU-004 | Verifikasi | Admin verifikasi data alumni | Data masuk perhitungan IKU, audit trail tercatat | BR-IKU-003 |
| TC-AUTH-001 | Keamanan | Login dengan password salah 5x | Akun terkunci 30 menit, notifikasi ke Super Admin | BR-SEC-005 |
| TC-SEC-001 | Audit | Admin coba hapus activity log | Sistem menolak, return ERR-SEC-001 | BR-SEC-001 |
| TC-CMP-001 | Compliance | Cek pertanyaan inti survey | Semua pertanyaan inti Belmawa ada dan tidak diubah | CMP-001 |
| TC-STK-001 | Stakeholder | Employer isi survey kompetensi | Data tersimpan dan masuk gap analysis CPL | BR-SUR-007 |
| TC-PWA-001 | Offline | Alumni isi survey tanpa koneksi | Data tersimpan lokal (localStorage), sync saat online | BR-SUR-005 |
| TC-CI3-001 | Framework | Cek kompatibilitas shared hosting | Aplikasi berjalan di PHP 7.4+ tanpa Redis/Docker | - |
| TC-CI3-002 | Cron | Cron job IKU berjalan otomatis | Perhitungan IKU berhasil jam 02:00 WIB | BR-IKU-004 |
| TC-CI3-003 | Queue | Export Excel 5000 record via queue | File tersimpan di folder uploads, notifikasi email terkirim | PERF-007 |

### 9.3 Traceability Matrix

| Req ID | Modul | Business Rule | API Endpoint | DB Table | Test Case | Code File |
|--------|-------|---------------|------------|----------|-----------|-----------|
| ALM-001 | Alumni | BR-ALM-002, BR-ALM-005 | POST /api/alumni | alumni, users | TC-ALM-001 | Alumni.php::store() |
| ALM-002 | Alumni | BR-ALM-003, BR-ALM-008 | PUT /api/alumni/{id} | alumni | TC-ALM-003 | Alumni.php::update() |
| ALM-003 | Alumni | BR-ALM-006 | POST /api/alumni/import | alumni | TC-ALM-001 | Alumni.php::import() |
| ALM-007 | Kohort | BR-ALM-001 | - | kohorts, alumni | TC-ALM-002 | Kohort_model::assign() |
| ALM-008 | Integrasi | BR-ALM-009 | POST /api/alumni/sync/pddikti | alumni | TC-ALM-001 | PddiktiSync.php::sync() |
| ALM-009 | Alumni | BR-ALM-004 | - | alumni | TC-ALM-003 | Alumni_model::calculateMasaTunggu() |
| ALM-010 | Verifikasi | BR-IKU-003 | POST /api/iku/verifikasi | verifikasi_data | TC-IKU-004 | Iku.php::verifikasi() |
| SUR-AL-002 | Survey | BR-SUR-003 | POST /api/surveys/{id}/logic | survey_logic | TC-SUR-002 | SurveyLogic.php::validate() |
| SUR-AL-007 | Survey | BR-SUR-001, BR-SUR-002 | DELETE /api/surveys/{id}/questions/{qid} | survey_questions | TC-SUR-004, TC-SUR-005 | Auth_lib::check_survey_delete() |
| SUR-AL-010 | Survey | BR-ALM-001, BR-ALM-007 | POST /api/surveys/{id}/responses | survey_responses | TC-SUR-003 | Survey.php::submitResponse() |
| SUR-AL-011 | Survey | BR-SUR-005 | POST /api/surveys/{id}/responses | survey_responses | TC-PWA-001 | survey-form.js (jQuery + localStorage) |
| IKU-001 | IKU | BR-IKU-004 | POST /api/iku/calculate | iku_calculations | TC-IKU-001 | IkuCalculator.php::calculate() |
| IKU-002 | IKU | BR-IKU-002, BR-IKU-007 | - | iku_calculations, alumni | TC-IKU-001 | IkuCalculator.php::calculate() |
| IKU-004 | IKU | BR-IKU-005, BR-IKU-006 | POST /api/iku/export/belmawa | iku_calculations | TC-IKU-003 | BelmawaTemplate.php::export() |
| AUTH-001 | Auth | BR-SEC-004 | POST /api/auth/login | users | TC-AUTH-001 | Auth.php::login() |
| AUTH-004 | Audit | BR-SEC-001 | - | activity_logs | TC-SEC-001 | AuditHook.php |
| INT-001 | Integrasi | BR-ALM-009, BR-SEC-003 | POST /api/alumni/sync/pddikti | alumni | TC-ALM-001 | PddiktiSync.php |

### 9.4 User Story & Acceptance Criteria

#### US-ALM-001: Registrasi Alumni

**Sebagai:** Alumni  
**Saya ingin:** Registrasi dengan NIM dan tahun lulus  
**Sehingga:** Saya bisa mengakses survey tracer study

**Acceptance Criteria:**
1. Diberikan NIM valid, saat alumni input NIM, maka sistem validasi ke database mahasiswa
2. Diberikan NIM tidak ditemukan, saat alumni submit, maka sistem tampilkan error "NIM tidak terdaftar"
3. Diberikan email sudah terdaftar, saat alumni submit, maka sistem tampilkan error "Email sudah digunakan"
4. Diberikan data valid, saat alumni submit, maka sistem kirim OTP ke email dan redirect ke halaman verifikasi
5. Diberikan OTP valid, saat alumni input, maka sistem aktifkan akun dan auto-assign ke kohort aktif

#### US-SUR-001: Mengisi Survey Alumni

**Sebagai:** Alumni  
**Saya ingin:** Mengisi survey tracer study dari HP  
**Sehingga:** Saya bisa melaporkan status karir saya

**Acceptance Criteria:**
1. Diberikan alumni login dan kohort aktif, saat buka survey, maka sistem tampilkan pertanyaan inti baku
2. Diberikan alumni pilih "Sudah Bekerja", saat lanjut, maka sistem tampilkan pertanyaan bidang pekerjaan
3. Diberikan alumni pilih "Belum Bekerja", saat lanjut, maka sistem skip pertanyaan pekerjaan
4. Diberikan koneksi terputus saat mengisi, saat alumni lanjut, maka sistem simpan jawaban di localStorage (jQuery)
5. Diberikan koneksi kembali, saat alumni buka survey, maka sistem sync data lokal ke server (jQuery AJAX)
6. Diberikan alumni submit, saat validasi lolos, maka sistem tampilkan "Terima kasih" dan kirim sertifikat PDF

#### US-IKU-001: Monitoring IKU-1 oleh Admin

**Sebagai:** Admin Pusat Karir  
**Saya ingin:** Melihat dashboard IKU-1 real-time  
**Sehingga:** Saya bisa monitoring capaian prodi

**Acceptance Criteria:**
1. Diberikan admin buka dashboard IKU, saat load, maka sistem tampilkan gauge score per prodi (dari file cache CI3)
2. Diberikan response rate < minimum, saat admin view, maka sistem tampilkan warna merah dan alert
3. Diberikan admin klik "Export Belmawa", saat minimum tercapai, maka sistem generate Excel via cron job dan kirim email
4. Diberikan admin klik "Verifikasi Data", saat buka, maka sistem tampilkan list alumni pending dengan filter
5. Diberikan admin approve verifikasi, saat submit, maka sistem update status dan trigger recalculate IKU (via cron atau direct)

#### US-SEC-001: Audit Trail

**Sebagai:** Super Admin  
**Saya ingin:** Melihat log aktivitas seluruh sistem  
**Sehingga:** Saya bisa audit perubahan data

**Acceptance Criteria:**
1. Diberikan Super Admin buka audit trail, saat load, maka sistem tampilkan log terbaru dengan pagination (CI3 Pagination)
2. Diberikan Super Admin filter by module, saat apply, maka sistem filter log sesuai modul
3. Diberikan Super Admin coba hapus log, saat klik delete, maka sistem menolak dengan pesan "Log tidak dapat dihapus"
4. Diberikan log > 5 tahun, saat cron run, maka sistem archive ke file ZIP (bukan S3/Glacier)

---

## 10. JADWAL, DELIVERABLES & DEPLOYMENT

### 10.1 Timeline Pengembangan (Agile - 6 Sprints)

| Sprint | Durasi | Deliverables |
|--------|--------|-------------|
| **Sprint 0** | Minggu 0 (Pre-sprint) | Tech setup, BRD finalization, API spec review, DB schema freeze |
| **Sprint 1** | Minggu 1-2 | Setup CI3 project, DB schema v3, Autentikasi, RBAC, Activity Log, Audit Trail |
| **Sprint 2** | Minggu 3-4 | Manajemen Alumni + Kohort, Sinkron PDDikti, Import/Export, Verifikasi |
| **Sprint 3** | Minggu 5-6 | Survey Builder v3 (Pertanyaan Inti Baku + Kustom), Logic Jump Engine, localStorage PWA |
| **Sprint 4** | Minggu 7-8 | Survey Execution (Mobile), Stakeholder Survey, Notifikasi, Offline Mode (jQuery) |
| **Sprint 5** | Minggu 9-10 | IKU-1 Calculator, Dashboard IKU, Export Template Belmawa, Verifikasi Data |
| **Sprint 6** | Minggu 11-12 | Laporan & Grafik, Kurikulum Gap Analysis, API Integration, UAT, Deployment |

### 10.2 Deliverables

| No | Deliverable | Format | PIC |
|----|-------------|--------|-----|
| 1 | Source Code | Git Repository (GitHub/GitLab) | Tech Lead |
| 2 | Database Schema & Migration Scripts | SQL + CI3 Migrations | Database Admin |
| 3 | API Documentation | Postman Collection / Markdown | Backend Dev |
| 4 | Technical Documentation | Markdown/PDF | System Analyst |
| 5 | User Manual (Admin, Alumni, Stakeholder) | PDF/Video Tutorial | UX Writer |
| 6 | Deployment Guide (Shared Hosting) | Markdown | DevOps |
| 7 | Test Report & Coverage | PHPUnit Report / PDF | QA Engineer |
| 8 | Bukti IKU-1 untuk Akreditasi | PDF + Excel Template | System Analyst |
| 9 | Laporan Evaluasi Diri BAN-PT Kriteria 9 | PDF | System Analyst |
| 10 | Security Audit Report | PDF (OWASP ASVS checklist) | Security Engineer |

### 10.3 Deployment Architecture (Shared Hosting Compatible)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    SHARED HOSTING / VPS ENVIRONMENT                          │
│                     (cPanel / Plesk / DirectAdmin)                         │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                     APACHE / NGINX (Shared)                         │   │
│  │              SSL Termination (Let's Encrypt via cPanel)             │   │
│  │                    .htaccess rewrite (CI3)                        │   │
│  └───────────────────────────────┬─────────────────────────────────────┘   │
│                                  │                                          │
│  ┌───────────────────────────────▼─────────────────────────────────────┐     │
│  │                    PHP 7.4+ / 8.0+ (FPM/CGI)                       │     │
│  │                     CodeIgniter 3 + HMVC                          │     │
│  │  - Web requests (index.php)                                       │     │
│  │  - API requests (index.php/api/)                                  │     │
│  │  - CLI requests (php index.php cron)                              │     │
│  └───────────────────────────────┬─────────────────────────────────────┘     │
│                                  │                                          │
│  ┌───────────────────────────────▼─────────────────────────────────────┐     │
│  │              CI3 FILE CACHE (application/cache/)                  │     │
│  │  - Session Store (file atau database)                             │     │
│  │  - Cache (IKU, Alumni, Questions)                                 │     │
│  │  - Query Result Cache                                             │     │
│  └───────────────────────────────┬─────────────────────────────────────┘     │
│                                  │                                          │
│  ┌───────────────────────────────▼─────────────────────────────────────┐     │
│  │              MySQL 5.7+ / MariaDB 10.3+ (Shared DB)               │     │
│  │  - Primary database (InnoDB)                                      │     │
│  │  - Daily backup (cron + mysqldump)                                │     │
│  │  - Read/Write tidak dipisah (shared hosting constraint)           │     │
│  └─────────────────────────────────────────────────────────────────────┘     │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐     │
│  │              CRON JOBS (cPanel Cron / CLI Controller)               │     │
│  │  - php index.php cron iku_calculate (02:00)                       │     │
│  │  - php index.php cron reminder (08:00)                            │     │
│  │  - php index.php cron pddikti_sync (01:00)                        │     │
│  │  - php index.php cron export_processor (setiap 5 menit)            │     │
│  │  - php index.php cron api_key_rotate (monthly)                    │     │
│  │  - php index.php cron log_archive (monthly)                       │     │
│  └─────────────────────────────────────────────────────────────────────┘     │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐     │
│  │              FILE STORAGE (Shared Hosting / Local)                    │     │
│  │  - uploads/avatars/ (Foto profil)                                   │     │
│  │  - uploads/exports/ (PDF, Excel)                                    │     │
│  │  - uploads/bukti/ (Verifikasi)                                      │     │
│  │  - application/logs/ (Error logs)                                   │     │
│  └─────────────────────────────────────────────────────────────────────┘     │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐     │
│  │              MONITORING (Sederhana - Email Based)                   │     │
│  │  - CI3 Log Library (application/logs/)                            │     │
│  │  - Email alert ke admin saat error critical                       │     │
│  │  - UptimeRobot (external uptime monitoring)                       │     │
│  └─────────────────────────────────────────────────────────────────────┘     │
└─────────────────────────────────────────────────────────────────────────────┘
```

**Cron Job Setup (cPanel):**
```bash
# IKU Calculation - Daily 02:00 WIB
0 2 * * * /usr/bin/php /home/user/public_html/index.php cron iku_calculate

# Reminder - Daily 08:00 WIB
0 8 * * * /usr/bin/php /home/user/public_html/index.php cron reminder

# PDDikti Sync - Daily 01:00 WIB
0 1 * * * /usr/bin/php /home/user/public_html/index.php cron pddikti_sync

# Export Processor - Every 5 minutes
*/5 * * * * /usr/bin/php /home/user/public_html/index.php cron export_processor

# API Key Rotation - Monthly
0 0 1 * * /usr/bin/php /home/user/public_html/index.php cron api_key_rotate

# Log Archive - Monthly
0 0 1 * * /usr/bin/php /home/user/public_html/index.php cron log_archive
```

**Deployment Checklist:**
- [ ] Upload file via FTP/Git ke public_html (tanpa folder system di web root)
- [ ] Konfigurasi database.php dengan credential hosting
- [ ] Konfigurasi config.php: base_url, encryption_key, session_driver
- [ ] Set folder permissions: uploads (755), application/cache (755), application/logs (755)
- [ ] Import database.sql via phpMyAdmin
- [ ] Setup cron jobs via cPanel
- [ ] Konfigurasi email (SMTP) di email.php
- [ ] SSL certificate aktif (Let's Encrypt via cPanel)
- [ ] .htaccess rewrite aktif (mod_rewrite)
- [ ] php.ini: upload_max_filesize ≥ 10M, post_max_size ≥ 10M, max_execution_time ≥ 300
- [ ] Backup script tested (mysqldump via cron)

### 10.4 Monitoring & Alerting (Sederhana - Email Based)

| Metrik | Tool | Threshold | Alert Channel |
|--------|------|-----------|---------------|
| **Error Rate** | CI3 Log Library | > 5 error/hour | Email ke Tech Lead |
| **Response Time (p95)** | Manual / Browser DevTools | > 3 detik | Email ke DevOps |
| **Queue Lag** | Database jobs table | > 100 jobs pending | Email ke Backend Dev |
| **Database Size** | phpMyAdmin / Custom script | > 80% quota hosting | Email ke DBA |
| **Disk Usage** | cPanel / Custom script | > 85% | Email ke SysAdmin |
| **Failed Login Attempts** | login_attempts table | > 10/menit | Email ke Security |
| **PDDikti Sync Failure** | Custom log | 3 consecutive fails | Email ke Admin Pusat Karir |
| **IKU Calculation Failure** | Custom log | Any failure | Email ke Admin Pusat Karir |
| **Backup Failure** | Cron log | Any failure | Email ke SysAdmin |

**Alert Severity Levels:**
- **P0 (Critical)**: Sistem down, data corruption, security breach → Immediate response (30 menit, shared hosting constraint)
- **P1 (High)**: IKU calculation gagal, PDDikti sync fail 3x → Response 2 jam
- **P2 (Medium)**: Queue lag, slow response → Response 8 jam
- **P3 (Low)**: Disk usage warning, backup delay → Response 24 jam

---

## 11. LAMPITAN

### Lampiran A: Daftar Pertanyaan Survey Default (Tracer Study Baku + Kustom)

**Bagian A: Data Pribadi (Auto-fill dari database)**
1. Nama Lengkap (Short Answer - auto-fill, readonly)
2. NIM (Short Answer - auto-fill, readonly)
3. Program Studi (Dropdown - auto-fill, readonly)
4. Tahun Lulus (Dropdown - auto-fill, readonly)
5. Email Aktif (Short Answer - validasi email)
6. Nomor HP (Short Answer - validasi numeric)

**Bagian B: Status Karir (Pertanyaan Inti Belmawa - 🔒 Locked)**
7. Apakah Anda sudah bekerja/melanjutkan studi/wirausaha? (Multiple Choice)
   - Sudah bekerja → Lanjut ke Q8
   - Wirausaha → Lanjut ke Q8B
   - Lanjut studi → Lanjut ke Q8C
   - Belum bekerja → Lanjut ke Q8D

8A. Nama Perusahaan/Instansi (Short Answer)
8A. Bidang Pekerjaan (Dropdown: IT, Keuangan, Pendidikan, Kesehatan, dll)
8A. Jabatan (Short Answer)
8A. Gaji Bulanan (Dropdown: <3jt, 3-5jt, 5-10jt, 10-20jt, >20jt)
8A. Berapa lama Anda mendapatkan pekerjaan pertama sejak lulus? (Number - dalam bulan)
8A. Kesesuaian Bidang Studi dengan Pekerjaan (Multiple Choice: Sesuai/Kurang/Tidak)

**Bagian C: Evaluasi Kompetensi (Pertanyaan Inti - Rating 1-5)**
14. Seberapa relevan mata kuliah berikut dengan pekerjaan Anda? (Rating per matkul)
15. Penguasaan kompetensi: Pengetahuan (Rating 1-5)
16. Penguasaan kompetensi: Keterampilan (Rating 1-5)
17. Penguasaan kompetensi: Sikap/Attitude (Rating 1-5)
18. Kompetensi apa yang perlu ditambahkan dalam kurikulum? (Long Answer)

**Bagian D: Evaluasi Proses (Pertanyaan Inti)**
19. Seberapa besar kontribusi perguruan tinggi terhadap kompetensi Anda? (Rating 1-5)
20. Saran perbaikan kurikulum dan pembelajaran (Long Answer)

**Bagian E: Pertanyaan Kustom Prodi (Dapat ditambah oleh Admin Prodi)**
21-XX. Pertanyaan tambahan sesuai kebutuhan prodi (tidak boleh mengganti/inteferensi pertanyaan inti)

### Lampiran B: Format Export PDF Laporan

HALAMAN SAMPUL
├── Logo Perguruan Tinggi
├── Judul: Laporan Tracer Study & IKU-1
├── Periode/Kohort: [Tahun]
├── Program Studi: [Nama Prodi]
├── Tanggal Generate
└── Status Verifikasi Data

HALAMAN EXECUTIVE SUMMARY
├── Total Alumni (Kohort): [X]
├── Total Responden: [Y]
├── Response Rate: [Z%]
├── Status Minimum Responden: [Tercapai/Belum]
├── IKU-1 Score: [Score]/100
├── Status Kerja: [Pie Chart]
├── Kesesuaian Kompetensi: [Radar Chart]
└── Rekomendasi Strategis

HALAMAN IKU-1 DETAIL
├── Tabel perhitungan bobot per responden
├── Grafik masa tunggu kerja
├── Grafik distribusi gaji vs UMP
└── Status verifikasi data

HALAMAN EVALUASI KURIKULUM
├── Gap Analysis CPL vs Industri (Heatmap)
├── Input Stakeholder/Employer
├── Rekomendasi perbaikan kurikulum
└── Rencana tindak lanjut PPEPP

### Lampiran C: Template Excel Pelaporan Nasional (Belmawa)

Format kolom wajib sesuai template tracerstudy.kemdikbud.go.id:
- Kolom A: NIM
- Kolom B: Nama
- Kolom C: Program Studi
- Kolom D: Tahun Lulus
- Kolom E: Status (1=Bekerja, 2=Wirausaha, 3=Lanjut Studi, 4=Belum Bekerja)
- Kolom F: Masa Tunggu (bulan)
- Kolom G: Gaji (kode range)
- Kolom H-X: Jawaban pertanyaan inti (kode sesuai panduan Belmawa)

### Lampiran D: API Endpoint Summary

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | /api/auth/login | Public | Login |
| POST | /api/auth/logout | API Key | Logout |
| GET | /api/alumni | API Key | List alumni |
| POST | /api/alumni | API Key | Create alumni |
| GET | /api/alumni/{id} | API Key | Detail alumni |
| PUT | /api/alumni/{id} | API Key | Update alumni |
| DELETE | /api/alumni/{id} | API Key | Delete alumni |
| POST | /api/alumni/import | API Key | Import Excel |
| POST | /api/alumni/sync/pddikti | API Key | Sync PDDikti |
| GET | /api/surveys | API Key | List surveys |
| POST | /api/surveys | API Key | Create survey |
| GET | /api/surveys/{id} | API Key | Detail survey |
| POST | /api/surveys/{id}/publish | API Key | Publish survey |
| GET | /api/surveys/{id}/questions | API Key | List questions |
| POST | /api/surveys/{id}/questions | API Key | Add question |
| DELETE | /api/surveys/{id}/questions/{qid} | API Key | Delete question |
| POST | /api/surveys/{id}/logic | API Key | Add logic jump |
| POST | /api/surveys/{id}/responses | API Key | Submit response |
| GET | /api/iku/dashboard | API Key | IKU dashboard |
| POST | /api/iku/calculate | API Key | Trigger calculation |
| POST | /api/iku/export/belmawa | API Key | Export template |
| POST | /api/iku/verifikasi | API Key | Verify data |
| POST | /api/webhooks/pddikti | API Key | PDDikti callback |
| POST | /api/webhooks/whatsapp | API Key | WA status callback |

---

## DAFTAR PERUBAHAN (REVISION HISTORY)

| Versi | Tanggal | Penulis | Perubahan |
|-------|---------|---------|-----------|
| 1.0 | 2026-06-10 | System Analyst | Dokumen awal |
| 2.0 | 2026-06-10 | Tim Pengembangan TS | Penambahan: Modul Kohort, IKU-1 Calculator, Survey Stakeholder, Pertanyaan Inti Belmawa, Integrasi PDDikti, Pelaporan Nasional, PWA, Compliance BAN-PT & SN-Dikti, Teknologi modern (Laravel 10/CI4), API, Audit Trail Immutable |
| 3.0 | 2026-06-11 | Tim Pengembangan TS | Penambahan: Business Rules Document (BRD) 25+ rules, Error Handling Matrix 20+ skenario, State Diagram & Workflow, Data Flow Diagram Level 0 & 1, Sequence Diagram, API Specification OpenAPI 3.0, Data Dictionary Lengkap, Database Partitioning & Caching Strategy, Security Threat Model STRIDE, Traceability Matrix, User Story & Acceptance Criteria, Deployment Architecture Diagram, Monitoring & Alerting Spec |
| **3.1** | **2026-06-27** | **Tim Pengembangan TS** | **Revisi Major Stack: Laravel 10 → CodeIgniter 3, Redis → File-based Cache CI3, Docker → Shared Hosting Compatible, Vanilla JS → jQuery 3.6, Queue (Redis) → Database Queue (tabel jobs) + Cron Job, Laravel Scheduler → CI3 CLI Controller + cPanel Cron, Laravel Sanctum → CI3 REST Controller + API Key, Laravel Eloquent → CI3 Query Builder, Laravel Blade → CI3 Views (PHP native), Laravel Migration → CI3 Migrations/Manual SQL, Laravel Encryption → CI3 Encryption Library, Monitoring (Sentry+Prometheus+Grafana) → CI3 Log + Email Alert, Deployment (Docker) → Shared Hosting (cPanel/Plesk)** |

**Disetujui oleh:**

| Peran | Nama | Tanda Tangan | Tanggal |
|-------|------|--------------|---------|
| Project Manager | ___________ | ___________ | ___________ |
| Tech Lead | ___________ | ___________ | ___________ |
| System Analyst Senior | ___________ | ___________ | ___________ |
| Client/Product Owner (Kepala Pusat Karir) | ___________ | ___________ | ___________ |
| Wakil Rektor Bidang Akademik/Kemahasiswaan | ___________ | ___________ | ___________ |
| Auditor SPMI/BAN-PT | ___________ | ___________ | ___________ |
