<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn('sso_server_url');
            $table->dropColumn('sso_realm');
            $table->dropColumn('sso_sip_identifier');
            $table->dropColumn('sso_public_key');
        });

        Schema::create('space_sso_servers', function (Blueprint $table) {
            $table->id();

            $table->string('server_url');
            $table->string('realm');
            $table->string('sip_identifier')->default('sip_identity');
            $table->text('public_key')->nullable();
            $table->string('client_id');
            $table->string('client_secret');
            $table->boolean('auto_provisioning')->nullable()->default(false);
            $table->string('role_provisioning')->nullable();

            $table->bigInteger('space_id')->unsigned();
            $table->foreign('space_id')->references('id')
                    ->on('spaces')->onDelete('cascade');

            $table->unique('space_id');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->string('sso_server_url')->nullable();
            $table->string('sso_realm')->nullable();
            $table->string('sso_sip_identifier')->default('sip_identity');
            $table->text('sso_public_key')->nullable();
        });

        Schema::dropIfExists('space_sso_servers');
    }
};
