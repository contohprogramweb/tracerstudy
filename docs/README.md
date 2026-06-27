# Documentation

Folder ini berisi dokumentasi lengkap untuk project Tracer Study.

## Daftar File Dokumentasi

### 1. **SRS_TracerStudi_V3.1_CI3.md**
Software Requirements Specification (SRS) - Dokumen spesifikasi lengkap sistem Tracer Study v3.1 dengan CodeIgniter 3.

### 2. **STRUCTURE.md**
Dokumentasi struktur folder dan arsitektur project HMVC CodeIgniter 3.

### 3. **CRON_SETUP.md**
Panduan setup cron jobs untuk sinkronisasi PDDikti dan automated tasks lainnya.

### 4. **README_EXPORT_BELMAWA.md**
Dokumentasi modul export template Belmawa untuk pelaporan IKU-1.

## Database Migrations

File SQL migration terletak di folder `/database/migrations/`:

- `001_pddikti_sync.sql` - Migration untuk sinkronisasi PDDikti
- `001_survey_builder.sql` - Migration untuk survey builder
- `002_survey_progress.sql` - Migration untuk tracking progress survey
- `database_migration_complete.sql` - Migration lengkap untuk kurikulum, CPL, dan notifikasi
- `database_migration_export.sql` - Migration untuk fitur export Belmawa
- `database_migration_kurikulum.sql` - Migration khusus modul kurikulum dan CPL

## Cara Menggunakan

### Menjalankan Migration

```bash
# Pilih file migration yang sesuai dengan kebutuhan
mysql -u username -p database_name < /workspace/database/migrations/database_migration_complete.sql
```

### Setup Cron Jobs

Lihat file `CRON_SETUP.md` untuk instruksi lengkap setup cron jobs.

