<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->string('keycloak_server_url')->nullable();
            $table->string('keycloak_realm')->nullable();
            $table->string('keycloak_sip_identifier')->default('sip_identity');
            $table->text('keycloak_public_key')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn('keycloak_server_url');
            $table->dropColumn('keycloak_realm');
            $table->dropColumn('keycloak_sip_identifier');
            $table->dropColumn('keycloak_public_key');
        });
    }
};
