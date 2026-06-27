# PDDikti Sync Cron Setup

## Daily Sync Schedule (01:00 WIB)

### Crontab Entry

Add the following line to your crontab (`crontab -e`):

```bash
0 1 * * * cd /workspace && php index.php sync cliPddikti >> /workspace/application/logs/pddikti_sync.log 2>&1
```

### Explanation

- `0 1 * * *` - Runs daily at 01:00 AM
- `cd /workspace` - Changes to application directory
- `php index.php sync cliPddikti` - Executes the sync CLI command
- `>> /workspace/application/logs/pddikti_sync.log 2>&1` - Logs output and errors

## Optional: Sync Specific Year or Prodi

```bash
# Sync year 2024 only
0 1 * * * cd /workspace && php index.php sync cliPddikti 2024 >> /workspace/application/logs/pddikti_sync.log 2>&1

# Sync specific prodi
0 1 * * * cd /workspace && php index.php sync cliPddikti 2024 PRODI_ID >> /workspace/application/logs/pddikti_sync.log 2>&1
```

## Maintenance: Cleanup Old Jobs

Add weekly cleanup job:

```bash
0 2 * * 0 cd /workspace && php index.php sync cleanup >> /workspace/application/logs/sync_cleanup.log 2>&1
```

## Monitoring

### Check Last Sync Log
```bash
tail -f /workspace/application/logs/pddikti_sync.log
```

### Check Job Status via Web
```
GET /sync/status/LAST_JOB_ID
```

### Check Pending Jobs
```bash
php index.php sync jobs 1 10
```

## Troubleshooting

### Permission Issues
```bash
chmod +x /workspace/index.php
chown -R www-data:www-data /workspace/application/logs
```

### Test Run (Manual Execution)
```bash
cd /workspace
php index.php sync cliPddikti 2024
```

### Check PHP CLI Version
```bash
php -v
```

## Environment Variables

Set these in your `.env` file:

```env
PDDIKTI_API_URL=https://neo.feeder.kemdikbud.go.id/rest
PDDIKTI_API_KEY=your_api_key_here
PDDIKTI_API_SECRET=your_api_secret_here
ADMIN_EMAIL=admin@university.ac.id
```

## Business Rules Implemented

| Rule | Description | Implementation |
|------|-------------|----------------|
| BR-ALM-009 | Data PDDikti precedence lebih tinggi dari manual | Library mergeData() method |
| BR-ALM-010 | Alumni nonaktif dikecualikan | syncBatch() skips inactive alumni |
| BR-SEC-003 | API Key rotasi otomatis 90 hari | shouldRotateApiKey() & rotateApiKey() |

## Error Handling

| Error Code | Description | Handling |
|------------|-------------|----------|
| ERR-ALM-002 | PDDikti API timeout | Queue retry 3x with exponential backoff |
| ERR-ALM-003 | Format tidak valid | Reject with error logging, downloadable template available |
