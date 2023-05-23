<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountCreationRequestTokensTable extends Migration
{
    public function up()
    {
        Schema::create('account_creation_request_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token', 16)->index();
            $table->boolean('used')->default(false);
            $table->dateTime('validated_at')->nullable();

            $table->bigInteger('acc_creation_token_id')->unsigned()->nullable();
            $table->foreign('acc_creation_token_id')->references('id')
                  ->on('account_creation_tokens')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('account_creation_request_tokens');
    }
}
