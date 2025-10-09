<?php

use App\Console\Commands\Accounts\ClearFiles;
use App\Console\Commands\Accounts\SendVoicemailsEmails;
use Illuminate\Support\Facades\Schedule;

Schedule::command(ClearFiles::class, [7, '--apply'])->daily();
Schedule::command(SendVoicemailsEmails::class)->everyMinute();
