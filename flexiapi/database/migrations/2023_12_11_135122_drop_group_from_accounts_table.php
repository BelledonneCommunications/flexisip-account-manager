<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropIndex('accounts_group_index');
            $table->dropColumn('group');
        });
    }

    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('group', 64)->nullable();
            $table->index('group');
        });
    }
};
