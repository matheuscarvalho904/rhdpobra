<?php

return [

    'backup' => [
        'name' => env('APP_NAME', 'erp-backup'),

        'source' => [

            'files' => [
                'include' => env('BACKUP_ONLY_DB', true)
                    ? []
                    : [
                        base_path(),
                    ],

                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                    storage_path('app/backup-temp'),
                    storage_path('framework/cache'),
                    storage_path('framework/sessions'),
                    storage_path('framework/views'),
                    storage_path('logs'),
                ],

                'follow_links' => false,
                'ignore_unreadable_directories' => false,

                // obrigatório na sua versão
                'relative_path' => null,
            ],

            'databases' => [
                env('DB_CONNECTION', 'mysql'),
            ],
        ],

        'destination' => [
            'filename_prefix' => 'backup_',

            'disks' => [
                env('BACKUP_DISK', 'local'),
                's3',
            ],
        ],

        'temporary_directory' => storage_path('app/backup-temp'),

        'password' => env('BACKUP_ARCHIVE_PASSWORD'),

        'encryption' => 'default',

        'timeout' => 300,
    ],

    /*
     * NOTIFICAÇÕES (TOTALMENTE COMPATÍVEL COM SUA VERSÃO)
     */
    'notifications' => [

        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification::class => [],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification::class => [],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification::class => [],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class => [],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification::class => [],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification::class => [],
        ],

        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,

        'mail' => [
            'to' => 'admin@localhost.com',
        ],

        'slack' => [
            'webhook_url' => '',
            'channel' => null,
            'username' => null,
            'icon' => null,
        ],
    ],

    /*
     * Monitoramento
     */
    'monitor_backups' => [
        [
            'name' => env('APP_NAME', 'erp-backup'),

            'disks' => [
                env('BACKUP_DISK', 'local'),
            ],

            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ],

    /*
     * Limpeza automática (ENTERPRISE)
     */
    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'default_strategy' => [
            'keep_all_backups_for_days' => env('BACKUP_RETENTION_DAYS', 15),
            'keep_daily_backups_for_days' => env('BACKUP_RETENTION_DAYS', 15),
            'keep_weekly_backups_for_weeks' => 4,
            'keep_monthly_backups_for_months' => 3,
            'keep_yearly_backups_for_years' => 1,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
    ],

];