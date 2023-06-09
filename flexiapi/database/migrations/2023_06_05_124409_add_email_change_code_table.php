<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailChangeCodeTable extends Migration
{
    public function up()
    {
        Schema::create('email_change_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id')->unsigned();
            $table->string('code');
            $table->string('email');
            $table->timestamps();

            $table->foreign('account_id')->references('id')
                  ->on('accounts')->onDelete('cascade');
        });

        Schema::dropIfExists('email_changed');
    }

    public function down()
    {
        Schema::dropIfExists('email_change_codes');

        Schema::create('email_changed', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id')->unsigned()->unique();
            $table->string('new_email');
            $table->string('hash');
            $table->timestamps();

            $table->foreign('account_id')->references('id')
                  ->on('accounts')->onDelete('cascade');
        });
    }
}
