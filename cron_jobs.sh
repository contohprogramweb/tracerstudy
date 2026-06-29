#!/bin/bash
# Cron Job untuk Sistem Notifikasi dan Reminder
# Tambahkan ke crontab dengan: crontab -e

# Proses Queue Notifikasi setiap 5 menit
*/5 * * * * cd /workspace && php index.php notification processQueue >> /var/log/cron_notif_queue.log 2>&1

# Kirim Reminder Survey setiap hari jam 08:00 WIB
0 8 * * * cd /workspace && php index.php notification sendReminders >> /var/log/cron_notif_reminder.log 2>&1

# Hitung IKU Daily (jika belum ada)
0 2 * * * cd /workspace && php index.php iku calculateAll >> /var/log/cron_iku.log 2>&1
