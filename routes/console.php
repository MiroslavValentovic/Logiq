<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Prvý deň v mesiaci o 00:05 automaticky uzamkne výkazy za predošlý mesiac
Schedule::command('reports:auto-lock-previous-month')->monthlyOn(1, '00:05');
