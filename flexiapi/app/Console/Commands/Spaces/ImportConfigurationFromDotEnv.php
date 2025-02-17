<?php

namespace App\Console\Commands\Spaces;

use Illuminate\Console\Command;
use App\Space;

class ImportConfigurationFromDotEnv extends Command
{
    protected $signature = 'spaces:import-configuration-from-dot-env {sip_domain}';
    protected $description = 'Import the deprecated space DotEnv configuration in a Space';

    public function handle()
    {
        $space = Space::where('domain', $this->argument('sip_domain'))->first();

        if (!$space) {
            $this->error('The space cannot be found');

            return 0;
        }

        $this->info('The following configuration will be imported in the space ' . $space->domain);
        $this->info('The existing settings will be overwritten:');

        $space->custom_theme = env('INSTANCE_CUSTOM_THEME', false);
        $space->web_panel = env('WEB_PANEL', true);

        $space->copyright_text = env('INSTANCE_COPYRIGHT', null);
        $space->intro_registration_text = env('INSTANCE_INTRO_REGISTRATION', null);
        $space->confirmed_registration_text = env('INSTANCE_CONFIRMED_REGISTRATION_TEXT', null);
        $space->newsletter_registration_address = env('NEWSLETTER_REGISTRATION_ADDRESS', null);
        $space->account_proxy_registrar_address = env('ACCOUNT_PROXY_REGISTRAR_ADDRESS', 'sip.domain.com');
        $space->account_realm = env('ACCOUNT_REALM', null);
        $space->custom_provisioning_overwrite_all = env('ACCOUNT_PROVISIONING_OVERWRITE_ALL', false);
        $space->provisioning_use_linphone_provisioning_header = env('ACCOUNT_PROVISIONING_USE_X_LINPHONE_PROVISIONING_HEADER', true);

        $space->public_registration = env('PUBLIC_REGISTRATION', true);
        $space->phone_registration = env('PHONE_AUTHENTICATION', true);
        $space->intercom_features = env('INTERCOM_FEATURES', false);

        foreach ($space->getDirty() as $key => $value) {
            $show = '    - ' . $key . ' => ';
            $show .= ($value == null) ? 'null' : $value;

            $this->info($show);
        }

        if ($this->confirm('Do you want to update ' . $space->domain . '?', false)) {
            $space->save();
            $this->info('Space updated');
        }
    }
}
