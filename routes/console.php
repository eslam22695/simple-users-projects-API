<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('tasks:notify-overdue')->dailyAt('08:00');
