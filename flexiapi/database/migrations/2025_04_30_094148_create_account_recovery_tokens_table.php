<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recovery_codes', function (Blueprint $table) {
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
        });

        Schema::create('account_recovery_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token');
            $table->string('pn_provider');
            $table->string('pn_param');
            $table->string('pn_prid');
            $table->boolean('used')->default(false);
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->integer('account_id')->unsigned()->nullable();
            $table->foreign('account_id')->references('id')
                ->on('accounts')->onDelete('cascade');
            $table->timestamps();

            $table->index('token');
            $table->index(['pn_provider', 'pn_param', 'pn_prid']);
        });
    }

    public function down(): void
    {
        Schema::table('recovery_codes', function (Blueprint $table) {
            $table->dropColumn('phone');
            $table->dropColumn('email');
        });

        Schema::dropIfExists('account_recovery_tokens');
    }
};
