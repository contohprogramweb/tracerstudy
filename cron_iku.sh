#!/bin/bash
# Cron Job untuk IKU Calculation
# Dijalankan setiap hari pukul 02:00 WIB (BR-IKU-004)

# Set environment
cd /path/to/your/application

# Execute IKU calculation for all active kohorts
php index.php iku calculateAll

# Log execution
echo "IKU Calculation completed at $(date '+%Y-%m-%d %H:%M:%S WIB')" >> /var/log/iku_calculation.log
