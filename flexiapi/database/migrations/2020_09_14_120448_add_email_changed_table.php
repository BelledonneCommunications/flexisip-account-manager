<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailChangedTable extends Migration
{
    public function up()
    {
        Schema::connection('local')->create('email_changed', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('account_id')->unsigned()->unique();
            $table->string('new_email');
            $table->string('hash');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('local')->dropIfExists('email_changed');
    }
}
