<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::rename('space_sso_servers', 'space_oidc_authentication_configurations');

        Schema::table('space_oidc_authentication_configurations', function (Blueprint $table) {
            $table->string('client_id')->nullable()->change();
            $table->string('client_secret')->nullable()->change();
        });

        Schema::create('space_digest_authentication_configurations', function (Blueprint $table) {
            $table->id();

            $table->string('realm');
            $table->string('default_password_algorithm', length: 10)->default('SHA-256');

            $table->bigInteger('space_id')->unsigned();
            $table->foreign('space_id')->references('id')
                ->on('spaces')->onDelete('cascade');

            $table->timestamps();
        });

        foreach (DB::table('spaces')->get() as $space) {
            DB::table('space_digest_authentication_configurations')->insert([
                'space_id' => $space->id,
                'realm' => $space->account_realm ?? $space->domain,
                'default_password_algorithm' => $space->account_default_password_algorithm ?? 'SHA-256',
                'created_at' => Carbon::now()
            ]);
        }

        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn('account_realm');
            $table->dropColumn('account_default_password_algorithm');
        });
    }

    public function down(): void
    {
        Schema::rename('space_oidc_authentication_configurations', 'space_sso_servers');

        Schema::table('space_sso_servers', function (Blueprint $table) {
            $table->string('client_id')->nullable(false)->change();
            $table->string('client_secret')->nullable(false)->change();
        });

        Schema::table('spaces', function (Blueprint $table) {
            $table->string('account_default_password_algorithm', length: 10)->default('SHA-256');
            $table->string('account_realm')->nullable();
        });

        foreach (DB::table('space_digest_authentication_configurations')->get() as $digestAuthenticationConfiguration) {
            $space = DB::table('spaces')->where('id', $digestAuthenticationConfiguration->space_id)->first();
            DB::table('spaces')->where('id', $digestAuthenticationConfiguration->space_id)->update(
                [
                    'account_realm' => $digestAuthenticationConfiguration->realm != $space->domain
                        ? $digestAuthenticationConfiguration->realm
                        : null,
                    'account_default_password_algorithm' => $digestAuthenticationConfiguration->default_password_algorithm,
                ]
            );
        }

        Schema::dropIfExists('space_digest_authentication_configurations');
    }
};
