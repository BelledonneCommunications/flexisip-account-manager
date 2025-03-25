<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('username', 64);
            $table->string('domain', 64);
            $table->string('password', 255);
            $table->string('algorithm', 10)->default('MD5');
            $table->string('realm', 64)->nullable();
            $table->string('registrar', 64)->nullable();
            $table->string('outbound_proxy', 64)->nullable();
            $table->string('protocol', 4)->default('UDP');

            $table->integer('account_id')->unsigned();
            $table->foreign('account_id')->references('id')
                    ->on('accounts')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_accounts');
    }
};
