<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('account_dictionary_entries', function (Blueprint $table) {
            $table->id();
            $table->string('key')->index();
            $table->string('value')->index();

            $table->integer('account_id')->unsigned();
            $table->foreign('account_id')->references('id')
                ->on('accounts')->onDelete('cascade');
            $table->unique(['account_id', 'key']);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('account_dictionary_entries');
    }
};
