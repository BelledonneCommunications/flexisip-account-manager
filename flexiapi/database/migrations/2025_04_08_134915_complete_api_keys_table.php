<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->integer('expires_after_last_used_minutes')->nullable();

            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['account_id']);
            }

            $table->dropUnique(['account_id']);

            if (DB::getDriverName() !== 'sqlite') {
                $table->foreign('account_id')->references('id')
                    ->on('accounts')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('expires_after_last_used_minutes');
            $table->unique('account_id');
        });
    }
};

