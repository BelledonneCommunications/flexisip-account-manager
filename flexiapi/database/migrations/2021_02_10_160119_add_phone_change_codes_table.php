<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhoneChangeCodesTable extends Migration
{
    public function up()
    {
        Schema::create('phone_change_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id')->unsigned();
            $table->string('code');
            $table->string('phone');
            $table->timestamps();

            $table->foreign('account_id')->references('id')
                  ->on('accounts')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('phone_change_codes');
    }
}
