<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigurationTable extends Migration
{
    public function up()
    {
        Schema::create('configuration', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('copyright')->nullable();
            $table->text('intro_registration')->nullable();
            $table->boolean('custom_theme')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('configuration');
    }
}
