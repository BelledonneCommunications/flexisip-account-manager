<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wizards', function (Blueprint $table) {
            $table->string('token', 8)->primary();

            $table->unsignedInteger('account_id');
            $table->unsignedInteger('provisioning_account_id');


            $table->foreign('account_id')
                  ->references('id')
                  ->on('accounts')
                  ->onDelete('cascade');

            $table->foreign('provisioning_account_id')
                   ->references('id')
                   ->on('accounts')
                   ->onDelete('cascade');

            $table->string('sip')->nullable();
            $table->enum('linphone_action', ['call', 'show', 'bye','accept', 'decline'])->nullable(); // peut être remplacer par 'Difficulty::cases()'
            $table->boolean('linphone_use_sips')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wizards');
    }
};
