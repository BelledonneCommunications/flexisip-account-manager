<?php

use App\AccountCreationToken;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakePnAttributesNullableAccountCreationTokensTable extends Migration
{
    public function up()
    {
        Schema::table('account_creation_tokens', function (Blueprint $table) {
            $table->string('pn_provider')->nullable(true)->change();
            $table->string('pn_param')->nullable(true)->change();
            $table->string('pn_prid')->nullable(true)->change();
        });
    }

    public function down()
    {
        AccountCreationToken::whereNull('pn_provider')->delete();

        Schema::table('account_creation_tokens', function (Blueprint $table) {
            $table->string('pn_provider')->nullable(false)->change();
            $table->string('pn_param')->nullable(false)->change();
            $table->string('pn_prid')->nullable(false)->change();
        });
    }
}
