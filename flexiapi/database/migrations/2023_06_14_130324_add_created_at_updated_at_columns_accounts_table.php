<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accounts', function(Blueprint $table) {
            $table->renameColumn('creation_time', 'created_at');
        });

        // Two different migrations to handle SQLite
        Schema::table('accounts', function(Blueprint $table) {
            $table->dateTime('updated_at')->nullable();
        });

        DB::statement('update accounts set updated_at = created_at');
    }

    public function down()
    {
        Schema::table('accounts', function(Blueprint $table) {
            $table->renameColumn('created_at', 'creation_time');
            $table->dropColumn('updated_at');
        });
    }
};
