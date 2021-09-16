<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueAccountsPasswordsAliasesTable extends Migration
{
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->unique(['username', 'domain']);
        });

        Schema::table('passwords', function (Blueprint $table) {
            $table->unique(['account_id', 'algorithm']);
        });

        Schema::table('aliases', function (Blueprint $table) {
            $table->unique(['alias', 'domain']);
        });
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropUnique(['username', 'domain']);
        });

        Schema::table('passwords', function (Blueprint $table) {
            $table->dropUnique(['account_id', 'algorithm']);
        });

        Schema::table('aliases', function (Blueprint $table) {
            $table->dropUnique(['alias', 'domain']);
        });

        Schema::enableForeignKeyConstraints();
    }
}
