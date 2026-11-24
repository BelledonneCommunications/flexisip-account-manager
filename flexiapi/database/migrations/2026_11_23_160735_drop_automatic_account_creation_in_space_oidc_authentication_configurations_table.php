<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::table('space_oidc_authentication_configurations')->delete();

        Schema::table('space_oidc_authentication_configurations', function (Blueprint $table) {
            $table->dropColumn('realm');
            $table->string('audience');
            $table->renameColumn('sip_identifier', 'sip_id_claim');
            $table->renameColumn('server_url', 'authorization_server');
            $table->dropColumn('auto_provisioning');
            $table->dropColumn('role_provisioning');
            $table->dropColumn('public_key');
        });

        Schema::create('space_oidc_authentication_configurations_keys', function (Blueprint $table) {
            $table->id();

            $table->string('kid');
            $table->text('public_key');

            $table->bigInteger('oidc_id')->unsigned();
            $table->foreign('oidc_id')->references('id')
                ->on('space_oidc_authentication_configurations')->onDelete('cascade');

            $table->unique(['id', 'kid']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        DB::table('space_oidc_authentication_configurations')->delete();

        Schema::table('space_oidc_authentication_configurations', function (Blueprint $table) {
            $table->string('realm');
            $table->dropColumn('audience');
            $table->renameColumn('sip_id_claim', 'sip_identifier');
            $table->renameColumn('authorization_server', 'server_url');
            $table->boolean('auto_provisioning')->nullable()->default(false);
            $table->string('role_provisioning')->nullable();
            $table->text('public_key');
        });

        Schema::dropIfExists('space_oidc_authentication_configurations_keys');
    }
};
