<?php

use App\Console\Commands\RecordBitcoinPrice;
use Illuminate\Support\Facades\Schedule;

Schedule::command('app:record-bitcoin')->everyTenMinutes();

Schedule::command('app:purge-old-bitcoin')->daily();
