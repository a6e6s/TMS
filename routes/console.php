<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('tickets:close-resolved')->dailyAt('02:00');
Schedule::command('payloads:prune')->dailyAt('03:00');
