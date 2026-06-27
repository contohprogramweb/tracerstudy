# Modul Export Template Belmawa

Modul ini berfungsi untuk mengekspor data tracer study sesuai format yang ditetapkan oleh Belmawa (tracerstudy.kemdikbud.go.id).

## File yang Dibuat

### 1. Library
- **`application/libraries/BelmawaTemplate.php`**
  - `generate($kohort_id, $prodi_id)` - Generate file Excel
  - `formatData($alumni_data)` - Format data sesuai template Belmawa
  - `validateFormat($file)` - Validasi format upload
  - `importFeedback($file)` - Import feedback dari Belmawa

### 2. Controller (Web)
- **`application/modules/iku/controllers/Export.php`**
  - `belmawa($kohort_id, $prodi_id)` - Trigger export
  - `download($filename)` - Download file hasil export
  - `status($job_id)` - Cek status background job
  - `form()` - Tampilan form export

### 3. Controller (CLI)
- **`application/controllers/cli/Export_cli.php`**
  - `run_all_pending()` - Process semua pending jobs
  - `retry_failed()` - Retry jobs yang gagal
  - `cleanup_old_files($days)` - Hapus file lama

### 4. View
- **`application/modules/iku/views/export.php`** - Form UI export dengan AJAX

### 5. Database Migration
- **`database_migration_export.sql`** - Script migrasi database

### 6. Cron Job
- **`cron_iku.sh`** - Updated untuk include export processing

## Format Kolom Excel

| Kolom | Field | Keterangan |
|-------|-------|------------|
| A | NIM | Nomor Induk Mahasiswa |
| B | Nama | Nama lengkap alumni |
| C | Program Studi | Nama prodi |
| D | Tahun Lulus | Tahun kelulusan |
| E | Status | 1=Bekerja, 2=Wirausaha, 3=Lanjut Studi, 4=Belum Bekerja |
| F | Masa Tunggu | Dalam bulan |
| G | Gaji | Kode range gaji |
| H-X | Q1-Q17 | Jawaban pertanyaan inti (kode sesuai panduan) |

## Business Rules Implementation

### BR-IKU-005: Export hanya oleh Admin Pusat Karir/Super Admin
```php
// Di Export controller constructor
$role = $this->session->userdata('role');
if (!in_array($role, ['admin_pusat_karir', 'super_admin'])) {
    show_error('Akses Ditolak', 403);
}
```

### BR-IKU-006: Data sudah dikirim tidak boleh diubah
```php
// Cek di BelmawaTemplate library
$already_sent = $this->CI->db->where('status', 'sent_to_belmawa')
                           ->get('export_logs')->row();
if ($already_sent) {
    return ['valid' => false, 'message' => 'Data immutable'];
}
```

### ERR-IKU-002: Response rate < minimum → dicegah
```php
if ($stats->response_rate < $min_threshold) {
    return ['valid' => false, 'message' => 'Response rate belum memenuhi minimum'];
}
```

### ERR-IKU-003: Data sudah dikirim → dicegah
Implementasi sama dengan BR-IKU-006.

## Cara Penggunaan

### Via Web Interface
1. Login sebagai Admin Pusat Karir atau Super Admin
2. Akses menu: IKU → Export Belmawa
3. Pilih Kohort dan Program Studi
4. Klik "Generate Export"
5. Tunggu proses selesai (AJAX polling)
6. Download file Excel

### Via CLI (Background Processing)
```bash
# Process semua pending export jobs
php index.php export_cli run_all_pending

# Retry jobs yang gagal
php index.php export_cli retry_failed

# Cleanup file lama (>30 hari)
php index.php export_cli cleanup_old_files 30
```

### Setup Cron Job
Edit `/etc/crontab`:
```bash
# Jalankan setiap hari pukul 02:00 WIB
0 2 * * * root /bin/bash /path/to/cron_iku.sh
```

## Dependencies

Pastikan PhpSpreadsheet terinstall:
```bash
composer require phpoffice/phpspreadsheet
```

Atau manual:
```bash
cd application/third_party
git clone https://github.com/PHPOffice/PhpSpreadsheet.git phpspreadsheet
cd phpspreadsheet
composer install
```

## Database Setup

Jalankan migration:
```bash
mysql -u username -p database_name < database_migration_export.sql
```

## Struktur Direktori

```
/workspace/
├── application/
│   ├── libraries/
│   │   └── BelmawaTemplate.php
│   ├── modules/
│   │   └── iku/
│   │       ├── controllers/
│   │       │   └── Export.php
│   │       └── views/
│   │           └── export.php
│   └── controllers/
│       └── cli/
│           └── Export_cli.php
├── uploads/
│   └── exports/          # Directory untuk file export
├── cron_iku.sh
└── database_migration_export.sql
```

## Error Handling

Library mengembalikan response array:
```php
[
    'status' => true/false,
    'file' => 'filename.xlsx' (jika sukses),
    'message' => 'Deskripsi error/sukses'
]
```

Error codes:
- `ERR-IKU-002`: Response rate below threshold
- `ERR-IKU-003`: Data already sent (immutable)
- `ERR-IKU-005`: Unauthorized access

## Testing

Test generate manual:
```php
$this->load->library('BelmawaTemplate');
$result = $this->BelmawaTemplate->generate(1, 1); // kohort_id=1, prodi_id=1
print_r($result);
```

Test CLI:
```bash
php index.php export_cli run_all_pending
```

## Audit Trail

Semua aktivitas dicatat di:
- `jobs` table - Tracking background jobs
- `export_logs` table - Log export dan pengiriman ke Belmawa
- `download_logs` table - Log download file

## Email Notification

Notifikasi otomatis dikirim saat:
- Export selesai diproses
- File siap diunduh

Konfigurasi email di `application/config/config.php`:
```php
$config['email_protocol'] = 'smtp';
$config['admin_email'] = 'admin@university.ac.id';
```
