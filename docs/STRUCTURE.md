# Struktur Project - Sistem Tracer Study Perguruan Tinggi v3.1

## Informasi Project
- **Framework**: CodeIgniter 3.x
- **Arsitektur**: HMVC (Hierarchical Model-View-Controller) dengan Modular Extensions
- **PHP Version**: 7.4+
- **Database**: MySQL/MariaDB

## Struktur Folder

```
/workspace
├── application/                  # Direktori aplikasi utama
│   ├── cache/                    # Cache files
│   ├── config/                   # Konfigurasi aplikasi
│   │   ├── autoload.php          # Autoload libraries, helpers, models
│   │   ├── config.php            # Konfigurasi utama (base_url, encryption_key, session)
│   │   ├── database.php          # Konfigurasi database
│   │   ├── hooks.php             # Konfigurasi hooks
│   │   └── routes.php            # Routing untuk HMVC
│   ├── controllers/              # Controllers global (opsional)
│   │   └── core/                 # Base controllers tambahan
│   ├── core/                     # Core classes yang di-override
│   │   ├── MY_Controller.php     # Base controller (MY_, Admin_, Public_, API_)
│   │   └── MY_Model.php          # Base model dengan CRUD methods
│   ├── helpers/                  # Custom helpers
│   ├── hooks/                    # Hook classes
│   │   ├── AuthHook.php          # Authentication check
│   │   ├── AuditHook.php         # Activity logging
│   │   └── MaintenanceHook.php   # Maintenance mode check
│   ├── libraries/                # Custom libraries
│   ├── logs/                     # Log files
│   ├── models/                   # Models global (opsional)
│   │   └── core/                 # Base models tambahan
│   ├── modules/                  # HMVC Modules
│   │   ├── alumni/               # Modul Alumni
│   │   │   ├── config/           # Module config
│   │   │   ├── controllers/      # Module controllers
│   │   │   ├── models/           # Module models
│   │   │   └── views/            # Module views
│   │   ├── auth/                 # Modul Authentication
│   │   ├── iku/                  # Modul IKU (Indikator Kinerja Utama)
│   │   ├── kurikulum/            # Modul Kurikulum
│   │   ├── laporan/              # Modul Laporan
│   │   ├── stakeholder/          # Modul Stakeholder
│   │   └── survey/               # Modul Survey
│   ├── third_party/              # Third party libraries
│   └── views/                    # Global views (layouts, errors)
├── public/                       # Web root (document root)
│   ├── .htaccess                 # Apache rewrite & security rules
│   ├── index.php                 # Front controller
│   ├── assets/                   # Static assets
│   │   ├── css/                  # Stylesheets
│   │   ├── js/                   # JavaScript files
│   │   └── img/                  # Images
│   └── uploads/                  # User uploads
│       ├── avatars/              # User avatar images
│       ├── exports/              # Exported files (Excel, PDF)
│       └── bukti/                # Bukti dokumen
├── system/                       # CodeIgniter system folder
├── vendor/                       # Composer dependencies (gitignored)
├── composer.json                 # Composer configuration
└── .gitignore                    # Git ignore rules
```

## Konfigurasi Utama

### Config.php
- **base_url**: Dinamis berdasarkan `$_SERVER['HTTP_HOST']` dan `$_SERVER['SCRIPT_NAME']`
- **encryption_key**: Generated menggunakan `base64_encode(random_bytes(32))`
- **session_driver**: `'database'` dengan table `ci_sessions`
- **index_page**: `''` (empty untuk clean URLs)
- **enable_hooks**: `TRUE`

### Database.php
- Driver: `mysqli`
- Charset: `utf8mb4`
- Collation: `utf8mb4_unicode_ci`
- Database: `tracer_study_v31`

### Autoload.php
- **Libraries**: `database`, `session`, `form_validation`
- **Helpers**: `url`, `file`, `security`, `form`, `text`, `date`

### Routes.php
Routes dikonfigurasi per module:
- `auth/*` - Authentication routes
- `alumni/*` - Alumni management
- `survey/*` - Survey management
- `iku/*` - IKU reporting
- `kurikulum/*` - Curriculum management
- `stakeholder/*` - Stakeholder management
- `laporan/*` - Report generation

## Hooks

### AuthHook
- `check_auth()`: Memeriksa autentikasi sebelum controller dieksekusi
- `set_user_data()`: Menyediakan data user ke semua views
- `check_role()`: Helper untuk pengecekan role

### AuditHook
- `log_activity()`: Mencatat aktivitas user ke tabel `audit_logs`
- Log: login, logout, create, update, delete, export, download, upload

### MaintenanceHook
- `check_maintenance()`: Cek mode maintenance dari config/file flag
- Admin dapat bypass maintenance mode
- Halaman maintenance custom dengan status 503

## Base Classes

### MY_Controller
Base controller dengan fitur:
- Session management
- CSRF protection
- Helper methods: `render()`, `redirect_with_message()`, `json_response()`, `upload_file()`

#### Extended Controllers:
- `Admin_Controller`: Untuk admin area dengan role check
- `Public_Controller`: Untuk public area tanpa auth
- `API_Controller`: Untuk API endpoints dengan JSON response

### MY_Model
Base model dengan CRUD operations:
- **Read**: `get_all()`, `get_by_id()`, `get_by()`, `count()`, `exists()`
- **Create**: `insert()`, `insert_batch()`
- **Update**: `update()`, `update_by()`, `update_many()`
- **Delete**: `delete()`, `delete_by()`, `delete_many()`
- **Soft Delete**: `restore()`, `only_trashed()`, `with_trashed()`
- **Transaction**: `begin_transaction()`, `commit_transaction()`, `rollback_transaction()`

## Composer Dependencies

```json
{
    "require": {
        "php": ">=7.4",
        "phpoffice/phpspreadsheet": "^1.29",
        "dompdf/dompdf": "^2.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.6"
    }
}
```

## Security Features

### .htaccess
- URL rewriting (remove index.php)
- Security headers (X-Frame-Options, X-XSS-Protection, CSP)
- Block access to sensitive files (.env, .git, .sql, .log)
- Prevent PHP execution in uploads directory
- Compression and caching

### Application Security
- CSRF protection enabled
- XSS filtering available
- Encrypted sessions
- Input validation via form_validation library
- Audit trail untuk semua aktivitas penting

## Installation Steps

1. Clone repository
2. Install dependencies: `composer install`
3. Configure database di `application/config/database.php`
4. Create database `tracer_study_v31`
5. Run migrations (jika ada)
6. Set permissions:
   ```bash
   chmod 755 public/uploads
   chmod 755 application/logs
   chmod 755 application/cache
   ```
7. Access via web browser

## Module Structure Example

Setiap module memiliki struktur:
```
modules/{module_name}/
├── config/
│   └── {module}_routes.php    # Module-specific routes (optional)
├── controllers/
│   └── {Module_name}.php      # Main controller
├── models/
│   └── {Module_name}_model.php
└── views/
    ├── index.php
    ├── create.php
    ├── edit.php
    └── view.php
```

## Next Steps (Belum Diimplementasi)

- [ ] Implementasi controllers untuk setiap module
- [ ] Implementasi models untuk setiap module
- [ ] Implementasi views dengan layout system
- [ ] Database migrations/schema
- [ ] Unit tests dengan PHPUnit
- [ ] API documentation
- [ ] Deployment scripts

