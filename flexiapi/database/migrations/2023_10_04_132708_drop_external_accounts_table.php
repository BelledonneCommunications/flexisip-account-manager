<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up()
        {
            Schema::dropIfExists('external_accounts');
        }

        public function down()
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
        }
};
