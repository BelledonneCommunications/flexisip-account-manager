<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vcards_storage', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index();
            $table->text('vcard');

            $table->integer('account_id')->unsigned();
            $table->foreign('account_id')->references('id')
                    ->on('accounts')->onDelete('cascade');

            $table->unique(['account_id', 'uuid']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vcards_storage');
    }
};
