<?php

namespace App\Console\Commands\Spaces;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

use App\Mail\ExpiringSpace;
use App\Space;

class ExpirationEmails extends Command
{
    protected $signature = 'spaces:expiration-emails {days?}';
    protected $description = 'Send an expiration email on the designated configured days before expiration. Days must be ordered descending and comma separated (eg. 7,3,1)';

    public function handle()
    {
        $days = ['7','3','1'];

        if ($this->argument('days')) {
            preg_match_all('/\d++/', $this->argument('days'), $matches);

            if (!empty($matches[0])) {
                $i = 0;

                while ($i + 1 < count($matches[0]) && (int)$matches[0][$i] > (int)$matches[0][$i + 1]) {
                    $i++;
                }

                if ($i != count($matches[0]) - 1) {
                    $this->error('The days must be integer, ordered descending and comma separated');

                    return Command::FAILURE;
                }

                $days = $matches[0];
            }
        }

        $expiringSpaces = Space::whereNotNull('expire_at')->whereDate('expire_at', '>=', Carbon::now())->get();

        foreach ($expiringSpaces as $expiringSpace) {
            if (in_array($expiringSpace->daysLeft, $days)) {
                $this->info($expiringSpace->name . ' (' . $expiringSpace->host . ') is expiring in ' . $expiringSpace->daysLeft . ' days');

                $admins = $expiringSpace->admins()->withoutGlobalScopes()->whereNotNull('email')->get();

                $this->info('Sending an email to the admins ' . $admins->implode('email', ','));

                foreach ($admins as $admin) {
                    Mail::to($admin->email)->send(new ExpiringSpace($expiringSpace));
                }
            }
        }
    }
}
