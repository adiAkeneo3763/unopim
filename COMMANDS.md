# UnoPim CLI Commands & Cron Setup

## Available Artisan Commands

### Elasticsearch Indexing

| Command | Description |
|---------|-------------|
| `php artisan unopim:product:index` | Re-index all products in Elasticsearch |
| `php artisan unopim:category:index` | Re-index all categories in Elasticsearch |
| `php artisan unopim:elastic:clear` | Clear all Elasticsearch indices |

### Completeness

| Command | Description |
|---------|-------------|
| `php artisan unopim:completeness:recalculate --all` | Recalculate completeness for all products |
| `php artisan unopim:completeness:recalculate --family=ID` | Recalculate completeness for a specific attribute family |
| `php artisan unopim:completeness:recalculate --product=ID` | Recalculate completeness for a single product |
| `php artisan unopim:completeness:recalculate --products=1 --products=2` | Recalculate completeness for specific product IDs |

### Dashboard

| Command | Description |
|---------|-------------|
| `php artisan unopim:dashboard:refresh` | Clear dashboard statistics cache so next page load shows fresh data |

### Data Transfer

| Command | Description |
|---------|-------------|
| `php artisan unopim:queue:work` | Process queued import/export jobs |

### System

| Command | Description |
|---------|-------------|
| `php artisan unopim:version` | Display the current UnoPim version |
| `php artisan unopim:publish` | Publish UnoPim assets and config (use `--force` to overwrite) |
| `php artisan unopim:install` | Run the interactive UnoPim installer |
| `php artisan unopim:user:create` | Create a default admin user |
| `php artisan unopim:passport:client` | Create an API OAuth client |
| `php artisan unopim:images:purge-unused` | Remove unused product images (use `--dry-run` to preview) |

---

## Cron Setup

UnoPim requires a single cron entry to run the Laravel scheduler, which handles all periodic tasks automatically.

### Add the Cron Entry

```bash
crontab -e
```

Add this line (replace `/path/to/unopim` with your actual installation path):

```
* * * * * cd /path/to/unopim && php artisan schedule:run >> /dev/null 2>&1
```

### Scheduled Tasks (Automatic)

The following tasks run automatically via the scheduler:

| Task | Schedule | Description |
|------|----------|-------------|
| `unopim:product:index` | Daily at 00:01 & 12:01 | Elasticsearch product re-indexing |
| `unopim:category:index` | Daily at 00:01 & 12:01 | Elasticsearch category re-indexing |
| `unopim:completeness:recalculate --all` | Daily at 02:00 | Recalculate product completeness scores |
| `unopim:dashboard:refresh` | Every 10 minutes | Refresh dashboard statistics cache |

### Queue Worker

For background job processing (imports, exports, completeness calculations, notifications), you need a queue worker running:

```bash
php artisan queue:work --queue=default,system --sleep=3 --tries=3
```

For production, use a process manager like Supervisor:

```ini
[program:unopim-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/unopim/artisan queue:work --queue=default,system --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/unopim/storage/logs/worker.log
stopwaitsecs=3600
```

---

## Notifications

### Completeness Notifications

When a bulk completeness calculation finishes, all admin users with full permissions are notified via:

1. **UI notification** - appears in the notification bell in the admin panel
2. **Email notification** - sent if SMTP mail is configured in `.env`

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `NOTIFICATIONS_ENABLED` | `true` | Enable/disable all notifications |
| `COMPLETENESS_QUEUE` | `system` | Queue name for completeness jobs |
