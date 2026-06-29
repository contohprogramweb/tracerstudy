#!/bin/bash
# Cron Job untuk IKU Calculation dan Export Belmawa
# Dijalankan setiap hari pukul 02:00 WIB (BR-IKU-004)

# Set environment
cd /path/to/your/application

# Execute IKU calculation for all active kohorts
php index.php iku calculateAll

# Process pending export jobs (background processing)
php index.php export_cli run_all_pending

# Cleanup old export files (older than 30 days)
php index.php export_cli cleanup_old_files 30

# Log execution
echo "IKU Calculation & Export completed at $(date '+%Y-%m-%d %H:%M:%S WIB')" >> /var/log/iku_calculation.log
