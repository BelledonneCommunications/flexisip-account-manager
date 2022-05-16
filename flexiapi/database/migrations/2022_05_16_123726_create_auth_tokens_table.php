<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthTokensTable extends Migration
{
    public function up()
    {
        Schema::create('auth_tokens', function (Blueprint $table) {
            $table->id();

            $table->integer('account_id')->unsigned()->nullable();
            $table->string('token', 32);

            $table->foreign('account_id')->references('id')
                  ->on('accounts')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('auth_tokens');
    }
}
