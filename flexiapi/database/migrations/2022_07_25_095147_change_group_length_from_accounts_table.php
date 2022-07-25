<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeGroupLengthFromAccountsTable extends Migration
{
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->string('group', 64)->change();
            }
        });
    }

    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->string('group', 16)->change();
            }
        });
    }
}
