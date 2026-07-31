<?php

use App\Console\Commands\Accounts\ClearAccountsTombstones;
use App\Console\Commands\Accounts\ClearApiKeys;
use App\Console\Commands\Accounts\ClearFiles;
use App\Console\Commands\Accounts\ClearUnconfirmed;
use App\Console\Commands\Accounts\SendVoicemailsEmails;
use App\Console\Commands\ClearStatistics;
use App\Console\Commands\Digest\ClearOpaques;
use App\Console\Commands\Spaces\ExpirationEmails;
use Illuminate\Support\Facades\Schedule;

Schedule::command(ClearFiles::class, [7, '--apply'])->daily();
Schedule::command(SendVoicemailsEmails::class)->everyMinute();
Schedule::command(ClearAccountsTombstones::class, [7, '--apply'])->daily();
Schedule::command(ClearApiKeys::class, [60])->daily();
Schedule::command(ClearFiles::class, [30, '--apply'])->daily();
Schedule::command(ClearUnconfirmed::class, [30, '--apply'])->daily();
Schedule::command(ClearStatistics::class, [30, '--apply'])->daily();
Schedule::command(ClearOpaques::class, [60])->daily();
Schedule::command(ExpirationEmails::class)->daily();
