<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->text('copyright_text')->nullable();
            $table->text('intro_registration_text')->nullable();
            $table->text('confirmed_registration_text')->nullable();

            $table->string('newsletter_registration_address')->nullable();
            $table->string('account_proxy_registrar_address')->nullable();
            $table->string('account_realm')->nullable();

            $table->text('custom_provisioning_entries')->nullable();
            $table->boolean('custom_provisioning_overwrite_all')->default(false);
            $table->boolean('provisioning_use_linphone_provisioning_header')->default(true);

            $table->boolean('custom_theme')->default(false);
            $table->boolean('web_panel')->default(true);
            $table->boolean('public_registration')->default(true);
            $table->boolean('phone_registration')->default(true);
            $table->boolean('intercom_features')->default(false);
        });

    }

    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn('copyright_text');
            $table->dropColumn('intro_registration_text');
            $table->dropColumn('confirmed_registration_text');

            $table->dropColumn('newsletter_registration_address');
            $table->dropColumn('account_proxy_registrar_address');
            $table->dropColumn('account_realm');

            $table->dropColumn('custom_provisioning_entries');
            $table->dropColumn('custom_provisioning_overwrite_all');
            $table->dropColumn('provisioning_use_linphone_provisioning_header');

            $table->dropColumn('custom_theme');
            $table->dropColumn('web_panel');
            $table->dropColumn('public_registration');
            $table->dropColumn('phone_registration');
            $table->dropColumn('intercom_features');
        });
    }
};
