# Cron Jobs & Background Processing System

Sistem cron jobs dan background processing untuk CodeIgniter 3 dengan queue-based job processing.

## 📁 Struktur File

```
application/
├── controllers/
│   └── cli/
│       └── Cron.php              # CLI controller untuk semua cron jobs
├── libraries/
│   └── JobQueue.php              # Library untuk queue management
├── models/
│   └── Survey_model.php          # Model untuk operasi survey (existing)
└── migrations/
    └── 001_create_jobs_tables.php # Migration untuk tabel jobs
```

## 🗄️ Database Schema

### Tabel `jobs`
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT(20) UNSIGNED | Primary key |
| queue | VARCHAR(50) | Queue name (export, notifications, etc) |
| payload | TEXT | JSON encoded job data |
| attempts | TINYINT(3) UNSIGNED | Number of retry attempts |
| reserved_at | TIMESTAMP | When job was picked up |
| available_at | TIMESTAMP | When job becomes available |
| created_at | TIMESTAMP | When job was created |

### Tabel `failed_jobs` (Dead Letter Queue)
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT(20) UNSIGNED | Primary key |
| job_id | BIGINT(20) UNSIGNED | Reference to original job |
| queue | VARCHAR(50) | Queue name |
| payload | TEXT | Original job payload |
| exception | TEXT | Error message |
| failed_at | TIMESTAMP | When job failed |

## 🚀 Available Cron Jobs

### 1. **iku_calculate** - IKU Metrics Calculation
Menghitung Indikator Kinerja Utama (IKU) alumni.

```bash
# Calculate for all alumni
php index.php cli/cron/iku_calculate

# Calculate for specific kohort
php index.php cli/cron/iku_calculate 5

# Calculate for specific prodi
php index.php cli/cron/iku_calculate null 3

# Calculate for specific kohort and prodi
php index.php cli/cron/iku_calculate 5 3
```

**Schedule:** Daily at 02:00 WIB

---

### 2. **reminder** - Survey Reminder Notifications
Mengirim reminder ke alumni yang belum mengisi survei.

```bash
php index.php cli/cron/reminder
```

**Schedule:** Daily at 08:00 WIB

**Channels:** Email + WhatsApp (if number available)

---

### 3. **pddikti_sync** - PDDikti Data Synchronization
Sinkronisasi data dengan API PDDikti.

```bash
# Sync current year
php index.php cli/cron/pddikti_sync

# Sync specific year
php index.php cli/cron/pddikti_sync 2024

# Sync specific prodi
php index.php cli/cron/pddikti_sync null 3

# Sync specific year and prodi
php index.php cli/cron/pddikti_sync 2024 3
```

**Schedule:** Daily at 01:00 WIB

---

### 4. **export_processor** - Export Job Processor
Memproses export requests dari queue (CSV, Excel, PDF).

```bash
php index.php cli/cron/export_processor
```

**Schedule:** Every 5 minutes

**Usage Example:**
```php
// Add export job to queue
$this->job_queue->push('export', [
    'export_type' => 'excel',
    'user_id' => 123,
    'filters' => ['year' => 2024]
]);
```

---

### 5. **api_key_rotate** - API Key Rotation
Rotasi otomatis API keys untuk keamanan.

```bash
php index.php cli/cron/api_key_rotate
```

**Schedule:** Monthly (1st day at 00:00)

**Features:**
- Rotates keys older than 30 days
- Keeps previous key for 7-day grace period
- Notifies users via email

---

### 6. **log_archive** - Log File Archiving
Archiving dan kompresi log files lama.

```bash
php index.php cli/cron/log_archive
```

**Schedule:** Monthly (1st day at 00:00)

**Features:**
- Archives logs older than 30 days
- Compresses with gzip
- Deletes archives older than 1 year

---

### 7. **notification_queue** - Notification Processor
Memproses notification requests dari queue.

```bash
php index.php cli/cron/notification_queue
```

**Schedule:** Every 5 minutes

**Usage Example:**
```php
// Email notification
$this->job_queue->push('notifications', [
    'type' => 'email',
    'to' => 'user@example.com',
    'subject' => 'Survey Reminder',
    'message' => 'Please complete your survey...'
]);

// Push notification
$this->job_queue->push('notifications', [
    'type' => 'push',
    'user_id' => 123,
    'title' => 'New Survey',
    'body' => 'You have a new survey to complete'
]);

// WhatsApp notification
$this->job_queue->push('notifications', [
    'type' => 'whatsapp',
    'phone' => '+628123456789',
    'message' => 'Reminder: Complete your survey'
]);
```

## 📋 JobQueue Library Usage

### Basic Operations

```php
// Load library
$this->load->library('jobqueue');

// Add job to queue
$job_id = $this->job_queue->push('queue_name', [
    'data' => 'value',
    'user_id' => 123
]);

// Add job with delay (available after 5 minutes)
$job_id = $this->job_queue->push('queue_name', $payload, 300);

// Get next job from queue
$job = $this->job_queue->pop('queue_name');

// Process job
$this->job_queue->process($job_id, function($payload, $job_id) {
    // Your processing logic here
    if ($success) {
        return true;
    } else {
        throw new Exception('Processing failed');
    }
});

// Get queue statistics
$stats = $this->job_queue->stats();           // All queues
$stats = $this->job_queue->stats('export');   // Specific queue

// Retry failed job
$this->job_queue->retry($failed_job_id);

// Cleanup old jobs
$this->job_queue->cleanup(7);  // Delete jobs older than 7 days
```

### Retry Logic

- **Max Attempts:** 3
- **Backoff Strategy:** Exponential (2, 4, 8 minutes)
- **After Max Attempts:** Moved to `failed_jobs` table (dead letter queue)
- **Alert:** Email sent to admin on final failure

## 🔧 Installation Steps

### 1. Run Migration
```bash
php index.php migrate Create_jobs_tables
```

Or manually run the SQL:
```sql
-- See application/migrations/001_create_jobs_tables.php
```

### 2. Configure Crontab

Edit crontab:
```bash
crontab -e
```

Add entries from `crontab_setup.txt`:
```bash
# IKU Calculation - Daily 02:00 WIB
0 2 * * * /usr/bin/php /home/user/public_html/index.php cli/cron/iku_calculate

# Reminder - Daily 08:00 WIB
0 8 * * * /usr/bin/php /home/user/public_html/index.php cli/cron/reminder

# PDDikti Sync - Daily 01:00 WIB
0 1 * * * /usr/bin/php /home/user/public_html/index.php cli/cron/pddikti_sync

# Export Processor - Every 5 minutes
*/5 * * * * /usr/bin/php /home/user/public_html/index.php cli/cron/export_processor

# Notification Queue - Every 5 minutes
*/5 * * * * /usr/bin/php /home/user/public_html/index.php cli/cron/notification_queue

# API Key Rotation - Monthly
0 0 1 * * /usr/bin/php /home/user/public_html/index.php cli/cron/api_key_rotate

# Log Archive - Monthly
0 0 1 * * /usr/bin/php /home/user/public_html/index.php cli/cron/log_archive
```

### 3. Set Permissions
```bash
chmod +x /home/user/public_html/index.php
chown -R www-data:www-data /home/user/public_html/application/logs
chown -R www-data:www-data /home/user/public_html/writable
```

### 4. Configure Email (for alerts)
In `application/config/config.php`:
```php
$config['admin_email'] = 'admin@yoursite.com';

$config['protocol'] = 'smtp';
$config['smtp_host'] = 'smtp.yoursite.com';
$config['smtp_user'] = 'noreply@yoursite.com';
$config['smtp_pass'] = 'your_password';
$config['smtp_port'] = 587;
$config['mailtype'] = 'html';
```

## 🧪 Testing

### Test Individual Jobs
```bash
# Test CLI access
php index.php cli/cron/iku_calculate

# Test with parameters
php index.php cli/cron/pddikti_sync 2024

# Test export processor
php index.php cli/cron/export_processor
```

### Test Queue System
```php
// In any controller or model
$this->load->library('jobqueue');

// Push test job
$job_id = $this->job_queue->push('test', ['message' => 'Hello World']);

// Check stats
print_r($this->job_queue->stats());
```

### Monitor Logs
```bash
# View application logs
tail -f application/logs/log-2024-01-15.php

# View cron logs
tail -f /var/log/cron

# Check for errors
grep "CRON ERROR" application/logs/*.php
```

## ⚠️ Error Handling

### Automatic Retry
- Jobs automatically retry up to 3 times
- Exponential backoff between attempts
- Failed jobs moved to dead letter queue

### Email Alerts
Admin receives email when:
- Job fails after all retry attempts
- Cron job encounters fatal error

### Logging
All operations logged to:
- Application logs (`application/logs/`)
- Database (for job status)

## 📊 Monitoring

### Check Queue Status
```php
// Get all queue statistics
$stats = $this->job_queue->stats();
echo "Pending jobs: " . $stats['export']['pending'];
echo "Failed jobs: " . $stats['failed_total'];
```

### View Failed Jobs
```sql
SELECT * FROM failed_jobs ORDER BY failed_at DESC LIMIT 10;
```

### Retry Failed Jobs
```bash
# From PHP
$this->job_queue->retry($job_id);

# Or directly in database
UPDATE jobs 
SET attempts = 0, reserved_at = NULL 
WHERE id IN (SELECT job_id FROM failed_jobs WHERE queue = 'export');
```

## 🔐 Security Notes

1. **CLI-Only Access:** Cron controller rejects non-CLI requests
2. **API Key Rotation:** Automatic monthly rotation with grace period
3. **Error Logging:** All errors logged but not exposed publicly
4. **Database Transactions:** Used where appropriate for data integrity

## 📝 Best Practices

1. **Idempotent Jobs:** Ensure jobs can be safely retried
2. **Timeout Handling:** Long-running jobs should handle timeouts
3. **Resource Cleanup:** Free resources in finally blocks
4. **Logging:** Log important steps for debugging
5. **Monitoring:** Regularly check failed_jobs table
6. **Testing:** Test jobs in staging before production

## 🆘 Troubleshooting

### Job Not Processing
1. Check if cron is running: `ps aux | grep cron`
2. Verify crontab entries: `crontab -l`
3. Check PHP CLI path: `which php`
4. Review logs: `tail -f application/logs/log-*.php`

### Memory Issues
```bash
# Increase PHP memory limit for CLI
echo "memory_limit = 512M" >> /etc/php/7.4/cli/conf.d/99-memory.ini
```

### Timeout Issues
```bash
# Increase max execution time
echo "max_execution_time = 300" >> /etc/php/7.4/cli/conf.d/99-timeout.ini
```

## 📞 Support

For issues or questions:
1. Check application logs first
2. Review failed_jobs table
3. Test jobs manually via CLI
4. Contact system administrator

---

**Version:** 1.0  
**Last Updated:** 2024  
**Framework:** CodeIgniter 3
