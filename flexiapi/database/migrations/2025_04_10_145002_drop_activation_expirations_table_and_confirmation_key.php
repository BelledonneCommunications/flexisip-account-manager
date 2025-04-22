<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('activation_expirations');

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('confirmation_key');
        });
    }

    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('confirmation_key', 14)->nullable();
        });

        Schema::create('activation_expirations', function (Blueprint $table) {
            $table->id();
            $table->integer('account_id')->unsigned();
            $table->dateTime('expires');
            $table->timestamps();

            $table->foreign('account_id')->references('id')
                  ->on('accounts')->onDelete('cascade');
        });
    }
};
