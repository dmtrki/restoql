<?php

use Label84\LogViewer\Support\LogViewerLevel;

return [

    /*
     * Route settings
     */
    'route' => [
        'prefix' => 'logviewer',
        'middleware' => ['web'],
    ],

    /*
     * View settings
     */
    'view' => [
        'title' => 'LogViewer',
        'items_per_page' => 50,
        'show_logger' => true,
    ],

    /*
     * The directory containing your log files.
     *
     * The Laravel storage/logs directory is set as default.
     */
    'log_files_directory' => storage_path('logs'),

    /*
     * The default log channel of your application.
     *
     * Supported channels: single, daily
     *
     * single => the package will use laravel.log
     * daily => the package will use latest daily log file created with the format laravel-YYYY-MM-DD.log
     */
    'log_channel' => 'daily',

    /*
     * Records created within the last x minutes specified, will be marked as 'new' with an icon.
     *
     * To disable set the value to 0.
     */
    'marked_as_new_in_minutes' => 10,

    /*
     * Minimum threshold level for records, everything below this threshold will be excluded.
     */
    'minimum_level' => LogViewerLevel::DEBUG,

    /*
     * Maximum allowed file size of the log file in MB. To prevent memory exhaustion errors.
     */
    'max_file_size' => 20,

    /*
     * CSS classes to be applied to the level badges in the views.
     */
    'classes' => [
        'debug' => 'bg-primary',
        'info' => 'bg-info',
        'notice' => 'bg-info',
        'warning' => 'bg-warning',
        'error' => 'bg-danger',
        'critical' => 'bg-danger',
        'alert' => 'bg-danger',
        'emergency' => 'bg-danger',
    ],
];
