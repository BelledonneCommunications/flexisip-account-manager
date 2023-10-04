<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('statistics_calls', function (Blueprint $table) {
            $table->index('from_domain');
            $table->index('from_username');
            $table->index('to_domain');
            $table->index('to_username');
        });

        Schema::table('statistics_messages', function (Blueprint $table) {
            $table->index('from_domain');
            $table->index('from_username');
        });

        Schema::table('statistics_message_devices', function (Blueprint $table) {
            $table->index('to_domain');
            $table->index('to_username');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->index('domain');
            $table->index('username');
        });
    }

    public function down()
    {
        Schema::table('statistics_calls', function (Blueprint $table) {
            $table->dropIndex('statistics_calls_from_domain_index');
            $table->dropIndex('statistics_calls_from_username_index');
            $table->dropIndex('statistics_calls_to_domain_index');
            $table->dropIndex('statistics_calls_to_username_index');
        });

        Schema::table('statistics_messages', function (Blueprint $table) {
            $table->dropIndex('statistics_messages_from_domain_index');
            $table->dropIndex('statistics_messages_from_username_index');
        });

        Schema::table('statistics_message_devices', function (Blueprint $table) {
            $table->dropIndex('statistics_message_devices_to_domain_index');
            $table->dropIndex('statistics_message_devices_to_username_index');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropIndex('accounts_domain_index');
            $table->dropIndex('accounts_username_index');
        });
    }
};
