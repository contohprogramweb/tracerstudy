<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| PDDikti NeoFeeder Configuration
| -------------------------------------------------------------------------
| 
| Configuration for PDDikti API synchronization
| 
| Business Rules:
| - BR-SEC-003: API Key rotation every 90 days
| 
| Error Handling:
| - ERR-ALM-002: API timeout with retry mechanism
| - ERR-ALM-003: Invalid data format rejection
*/

$config['pddikti'] = [
    // PDDikti NeoFeeder API URL
    'api_url' => env('PDDIKTI_API_URL', 'https://neo.feeder.kemdikbud.go.id/rest'),
    
    // API Key (should be rotated every 90 days per BR-SEC-003)
    'api_key' => env('PDDIKTI_API_KEY', ''),
    
    // API Secret for request signing
    'api_secret' => env('PDDIKTI_API_SECRET', ''),
    
    // Connection timeout in seconds
    'timeout' => 30,
    
    // Maximum retry attempts for failed requests (ERR-ALM-002)
    'max_retries' => 3,
    
    // Delay between retries in seconds
    'retry_delay' => 5,
    
    // Enable/disable automatic sync
    'auto_sync_enabled' => true,
    
    // Default year to sync if not specified
    'default_tahun_lulus' => date('Y'),
    
    // Years range to sync (for batch operations)
    'tahun_lulus_range' => [
        'min' => 2015,
        'max' => date('Y')
    ],
    
    // Sync schedule (cron expression)
    'sync_schedule' => '0 1 * * *', // Daily at 01:00 WIB
    
    // Data precedence setting (BR-ALM-009)
    // 'pddikti' = PDDikti data always wins
    // 'manual' = Manual data always wins
    // 'merge' = Smart merge (PDDikti for critical fields)
    'data_precedence' => 'pddikti',
    
    // Fields that must come from PDDikti (cannot be overridden manually)
    'protected_fields' => [
        'nim',
        'nama',
        'tanggal_yudisium',
        'tahun_lulus',
        'no_ijazah',
        'no_sk_yudisium',
        'ipk',
        'status_aktif'
    ],
    
    // Enable logging of all sync operations
    'enable_logging' => true,
    
    // Log level: 'debug', 'info', 'warning', 'error'
    'log_level' => 'info',
    
    // Email notifications for sync failures
    'notify_on_failure' => true,
    'notification_email' => env('ADMIN_EMAIL', 'admin@university.ac.id'),
    
    // Queue settings for large batches
    'use_queue' => true,
    'batch_size' => 1000,
    
    // Skip inactive alumni (BR-ALM-010)
    'skip_inactive' => true
];

// Last API key rotation timestamp (for BR-SEC-003 tracking)
$config['pddikti_api_last_rotation'] = env('PDDIKTI_API_LAST_ROTATION', null);

// Sync job settings
$config['sync_jobs'] = [
    // Maximum concurrent jobs
    'max_concurrent' => 3,
    
    // Job timeout in seconds
    'job_timeout' => 3600, // 1 hour
    
    // Retry failed jobs
    'retry_failed_jobs' => true,
    'max_job_retries' => 3,
    
    // Cleanup old jobs after (days)
    'cleanup_after_days' => 30
];

/* End of file pddikti.php */
/* Location: ./application/config/pddikti.php */
