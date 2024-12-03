<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reset_password_email_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token', 16);
            $table->boolean('used')->default(false);
            $table->string('email');
            $table->integer('account_id')->unsigned();
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')
                  ->on('accounts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reset_password_email_tokens');
    }
};
