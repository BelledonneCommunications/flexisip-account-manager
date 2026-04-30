<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->string('sso_server_url')->nullable();
            $table->string('sso_realm')->nullable();
            $table->string('sso_sip_identifier')->default('sip_identity');
            $table->text('sso_public_key')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn('sso_server_url');
            $table->dropColumn('sso_realm');
            $table->dropColumn('sso_sip_identifier');
            $table->dropColumn('sso_public_key');
        });
    }
};
