<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeAccountTypesKeyUnique extends Migration
{
    public function up()
    {
        Schema::table('account_types', function (Blueprint $table) {
            $table->string('key', 64)->change();
            $table->unique('key');
        });

    }

    public function down()
    {
        Schema::table('account_types', function (Blueprint $table) {
            $table->dropUnique('account_types_key_unique');
            $table->string('key', 256)->change();
        });

    }
}
