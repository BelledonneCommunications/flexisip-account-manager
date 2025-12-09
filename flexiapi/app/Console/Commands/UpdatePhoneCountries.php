<?php

namespace App\Console\Commands;

use App\PhoneCountry;
use Illuminate\Console\Command;
use libphonenumber\PhoneNumberUtil;

class UpdatePhoneCountries extends Command
{
    protected $signature = 'app:update-phone-countries';
    protected $description = 'Update the phone_countries table from the getCountryCodes() function';

    public function handle()
    {
        $phoneNumberUtils = PhoneNumberUtil::getInstance();
        $countryCodes = getCountryCodes();

        foreach (array_diff(
            array_keys($countryCodes),
            PhoneCountry::pluck('code')->toArray()
        ) as $code) {
            if ($resolvedMetadata = $phoneNumberUtils->getMetadataForRegion($code)) {
                $phoneCountry = new PhoneCountry();
                $phoneCountry->code = $code;
                $phoneCountry->country_code = $resolvedMetadata->getCountryCode();
                $phoneCountry->save();

                $this->info($code . ' - ' . $countryCodes[$code] . '  inserted');
            }
        }
    }
}
