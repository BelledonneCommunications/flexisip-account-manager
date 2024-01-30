<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('recovery_codes', function (Blueprint $table) {
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
        });

        Schema::table('phone_change_codes', function (Blueprint $table) {
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
        });

        Schema::table('email_change_codes', function (Blueprint $table) {
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
        });

        Schema::table('provisioning_tokens', function (Blueprint $table) {
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
        });

        Schema::table('auth_tokens', function (Blueprint $table) {
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
        });

        Schema::table('account_creation_tokens', function (Blueprint $table) {
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
        });

        Schema::table('account_creation_request_tokens', function (Blueprint $table) {
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
        });
    }

    public function down()
    {
        Schema::table('recovery_codes', function (Blueprint $table) {
            $table->dropColumn('ip');
            $table->dropColumn('user_agent');
        });

        Schema::table('phone_change_codes', function (Blueprint $table) {
            $table->dropColumn('ip');
            $table->dropColumn('user_agent');
        });

        Schema::table('email_change_codes', function (Blueprint $table) {
            $table->dropColumn('ip');
            $table->dropColumn('user_agent');
        });

        Schema::table('provisioning_tokens', function (Blueprint $table) {
            $table->dropColumn('ip');
            $table->dropColumn('user_agent');
        });

        Schema::table('auth_tokens', function (Blueprint $table) {
            $table->dropColumn('ip');
            $table->dropColumn('user_agent');
        });

        Schema::table('account_creation_tokens', function (Blueprint $table) {
            $table->dropColumn('ip');
            $table->dropColumn('user_agent');
        });

        Schema::table('account_creation_request_tokens', function (Blueprint $table) {
            $table->dropColumn('ip');
            $table->dropColumn('user_agent');
        });
    }
};
