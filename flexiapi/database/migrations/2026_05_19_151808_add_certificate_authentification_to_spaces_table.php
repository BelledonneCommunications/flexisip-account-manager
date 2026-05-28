<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->boolean('client_certificate_authentication')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn('client_certificate_authentication');
        });
    }
};
