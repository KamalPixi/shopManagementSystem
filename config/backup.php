<?php

return [
    'enabled' => (bool) env('S3_BACKUP_ENABLED', false),
    'disk' => env('S3_BACKUP_DISK', 's3'),
    'prefix' => env('S3_BACKUP_PREFIX', 'shop-management/backups'),
    'retention' => (int) env('S3_BACKUP_RETENTION', 30),
    'schedule' => env('S3_BACKUP_SCHEDULE', '02:00'),
];
