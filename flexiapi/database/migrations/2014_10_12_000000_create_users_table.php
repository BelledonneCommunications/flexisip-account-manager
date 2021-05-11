<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::connection('local')->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email', 160)->unique(); // Because we (still) need to support MySQL 5.5 and its 767 bytes limit ¯\_(ツ)_/¯
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('local')->dropIfExists('users');
    }
}
