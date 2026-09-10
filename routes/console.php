<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Nreljed\DashboardCore\Services\FeatureRegistry;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('schedule:publish')->everyMinute();

foreach (app(FeatureRegistry::class)->getAllScheduledTasks() as $task) {
    $schedule = Schedule::command($task['command']);

    $frequency = $task['frequency'];

    if (str_starts_with($frequency, 'every:')) {
        $schedule->everyMinutes((int) substr($frequency, 6));
    } else {
        match ($frequency) {
            'everyMinute' => $schedule->everyMinute(),
            'everyFiveMinutes' => $schedule->everyFiveMinutes(),
            'hourly' => $schedule->hourly(),
            'daily' => $schedule->daily(),
            'weekly' => $schedule->weekly(),
            'monthly' => $schedule->monthly(),
            default => $schedule->hourly(),
        };
    }

    $schedule->withoutOverlapping()->name('feature:'.$task['provider'].':'.$task['command']);
}
