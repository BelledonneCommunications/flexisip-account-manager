<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sip_domains', function (Blueprint $table) {
            $table->boolean('disable_chat_feature')->default(false);
            $table->boolean('disable_meetings_feature')->default(false);
            $table->boolean('disable_broadcast_feature')->default(true);
            $table->integer('max_account')->default(0);
            $table->boolean('hide_settings')->default(false);
            $table->boolean('hide_account_settings')->default(false);
            $table->boolean('disable_call_recordings_feature')->default(false);
            $table->boolean('only_display_sip_uri_username')->default(false);
            $table->boolean('assistant_hide_create_account')->default(false);
            $table->boolean('assistant_disable_qr_code')->default(false);
            $table->boolean('assistant_hide_third_party_account')->default(false);
        });
    }

    public function down()
    {
        Schema::table('sip_domains', function (Blueprint $table) {
            $table->dropColumn('disable_chat_feature');
            $table->dropColumn('disable_meetings_feature');
            $table->dropColumn('disable_broadcast_feature');
            $table->dropColumn('max_account');
            $table->dropColumn('hide_settings');
            $table->dropColumn('hide_account_settings');
            $table->dropColumn('disable_call_recordings_feature');
            $table->dropColumn('only_display_sip_uri_username');
            $table->dropColumn('assistant_hide_create_account');
            $table->dropColumn('assistant_disable_qr_code');
            $table->dropColumn('assistant_hide_third_party_account');
        });
    }
};
