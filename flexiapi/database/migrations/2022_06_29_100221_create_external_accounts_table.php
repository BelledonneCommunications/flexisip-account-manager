<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExternalAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('external_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('username', 64);
            $table->string('domain', 64);
            $table->string('group', 16);
            $table->string('password', 255);
            $table->string('algorithm', 10)->default('MD5');
            $table->boolean('used')->default(false);

            $table->integer('account_id')->unsigned()->nullable();
            $table->foreign('account_id')->references('id')
                    ->on('accounts')->onDelete('set null');

            $table->timestamps();
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->string('group')->nullable();
            $table->index('group');
        });
    }

    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropIndex('accounts_group_index');
            $table->dropColumn('group');
        });

        Schema::dropIfExists('external_accounts');
    }
}
