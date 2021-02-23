<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivationExpirationsTable extends Migration
{
    public function up()
    {
        Schema::connection('local')->create('activation_expirations', function (Blueprint $table) {
            $table->id();
            $table->integer('account_id')->unsigned();
            $table->dateTime('expires');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('local')->dropIfExists('activation_expirations');
    }
}
