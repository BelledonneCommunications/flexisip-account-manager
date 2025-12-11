<?php

namespace App\Console\Commands\Accounts;

use App\AccountFile;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ClearFiles extends Command
{
    protected $signature = 'accounts:clear-files {days} {--apply}';
    protected $description = 'Remove the uploaded files after n days';

    public function handle(): int
    {
        $files = AccountFile::where(
            'created_at',
            '<',
            Carbon::now()->subDays($this->argument('days'))->toDateTimeString()
        );

        $count = $files->count();

        if ($this->option('apply')) {
            $this->info($count . ' files in deletion…');
            $files->delete();
            $this->info($count . ' files deleted');

            return Command::SUCCESS;
        }

        $this->info($count . ' files to delete');
        return Command::SUCCESS;
    }
}
