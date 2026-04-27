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
            $table->string('sso_client_id')->nullable();
            $table->string('sso_client_secret')->nullable();
            $table->boolean('sso_auto_prov')->nullable()->default(false);
            $table->string('sso_role_prov')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn('sso_server_url');
            $table->dropColumn('sso_realm');
            $table->dropColumn('sso_sip_identifier');
            $table->dropColumn('sso_public_key');
            $table->dropColumn('sso_client_id');
            $table->dropColumn('sso_client_secret');
            $table->dropColumn('sso_auto_prov');
            $table->dropColumn('sso_role_prov');
        });
    }
};
