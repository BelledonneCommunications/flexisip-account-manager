<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MoveProtocolFromAccountActionsToAccount extends Migration
{
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('dtmf_protocol')->nullable();
        });

        // See 2021_10_13_092937_create_contacts_table.php
        Schema::table('account_actions', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropColumn('protocol');
            }
        });
    }

    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropColumn('dtmf_protocol');
            }
        });

        Schema::table('account_actions', function (Blueprint $table) {
            $table->string('protocol');
        });
    }
}
