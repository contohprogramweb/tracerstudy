#!/bin/bash
# Cron Job untuk Sistem Notifikasi
# Tambahkan ke crontab dengan: crontab -e

# Path ke aplikasi CodeIgniter Anda
APP_PATH="/var/www/html/tracerstudy"
cd $APP_PATH

# 1. Proses Queue Notifikasi setiap 5 menit
*/5 * * * * cd $APP_PATH && php index.php notification processQueue >> logs/cron_notification_queue.log 2>&1

# 2. Kirim Reminder Survey setiap hari jam 08:00 WIB
0 8 * * * cd $APP_PATH && php index.php notification sendReminders >> logs/cron_notification_reminder.log 2>&1
