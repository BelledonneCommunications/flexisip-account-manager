<?php

use App\PhoneCountry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use libphonenumber\PhoneNumberUtil;

return new class extends Migration
{
    public function up()
    {
        Schema::create('phone_countries', function (Blueprint $table) {
            $table->string('code', 2)->primary();
            $table->string('country_code', 3);
            $table->boolean('activated')->default(false);
            $table->timestamps();
        });

        $phoneNumberUtils = PhoneNumberUtil::getInstance();
        foreach (getCountryCodes() as $code => $name) {
            $phoneCountry = new PhoneCountry();
            $phoneCountry->code = $code;
            $phoneCountry->country_code = $phoneNumberUtils->getMetadataForRegion($code)->getCountryCode();
            $phoneCountry->save();
        }
    }

    public function down()
    {
        Schema::dropIfExists('phone_countries');
    }
};
