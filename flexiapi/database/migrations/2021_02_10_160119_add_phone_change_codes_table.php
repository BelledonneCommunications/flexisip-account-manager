<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhoneChangeCodesTable extends Migration
{
    public function up()
    {
        Schema::connection('local')->create('phone_change_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('account_id')->unsigned();
            $table->string('code');
            $table->string('phone');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('local')->dropIfExists('phone_change_codes');
    }
}
