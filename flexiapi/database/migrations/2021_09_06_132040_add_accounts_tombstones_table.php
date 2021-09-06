<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountsTombstonesTable extends Migration
{
    public function up()
    {
        Schema::create('accounts_tombstones', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('domain');
            $table->timestamps();

            $table->unique(['username', 'domain']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounts_tombstones');
    }
}
