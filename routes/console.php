<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Delete activity logs older than 90 days — runs daily
Schedule::command('admin:cleanup-logs')->daily();

// Keep only the last 10 backups — runs daily
Schedule::command('admin:cleanup-backups')->daily();
