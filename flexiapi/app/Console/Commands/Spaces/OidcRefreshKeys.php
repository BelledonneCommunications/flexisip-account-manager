<?php

namespace App\Console\Commands\Spaces;

use App\Space;
use Illuminate\Console\Command;

class OidcRefreshKeys extends Command
{
    protected $signature = 'spaces:oidc-refresh-keys';
    protected $description = 'Refresh the Spaces OIDC configurations keys';

    public function handle()
    {
        $oidcSpaces = Space::has('oidcAuthenticationConfiguration')->get();

        foreach ($oidcSpaces as $oidcSpace) {
            $count = $oidcSpace->oidcAuthenticationConfiguration->refreshOIDCCertificate();

            $this->info($count > 0
                ? $oidcSpace->domain . ' ' . $count . ' certificates refreshed'
                : $oidcSpace->domain . ' no certificates found');
        }
    }
}
