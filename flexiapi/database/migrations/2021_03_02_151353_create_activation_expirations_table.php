<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivationExpirationsTable extends Migration
{
    public function up()
    {
        Schema::create('activation_expirations', function (Blueprint $table) {
            $table->id();
            $table->integer('account_id')->unsigned();
            $table->dateTime('expires');
            $table->timestamps();

            $table->foreign('account_id')->references('id')
                  ->on('accounts')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activation_expirations');
    }
}
