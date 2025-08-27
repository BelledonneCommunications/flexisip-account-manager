<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->boolean('carddav_user_credentials')->default(false);
        });

        Schema::create('space_carddav_servers', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('space_id')->unsigned();
            $table->foreign('space_id')->references('id')
                ->on('spaces')->onDelete('cascade');

            $table->boolean('enabled')->default(true);
            $table->string('uri');
            $table->integer('min_characters')->default(3);
            $table->integer('results_limit')->default(0);
            $table->integer('timeout')->default(5);
            $table->integer('delay')->default(500);
            $table->string('fields_for_user_input')->nullable();
            $table->string('fields_for_domain')->nullable();
            $table->boolean('use_exact_match_policy')->default(false);
            $table->timestamps();
        });

        Schema::create('account_carddav_credentials', function (Blueprint $table) {
            $table->string('username', 64);
            $table->string('password', 255);
            $table->string('realm', 255);
            $table->string('algorithm', 10)->default('MD5');

            $table->bigInteger('space_carddav_server_id')->unsigned();
            $table->foreign('space_carddav_server_id')->references('id')
                ->on('space_carddav_servers')->onDelete('cascade');

            $table->integer('account_id')->unsigned();
            $table->foreign('account_id')->references('id')
                ->on('accounts')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['space_carddav_server_id', 'account_id'], 'account_carddav_credentials_unique');
        });
    }

    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn('carddav_user_credentials');
        });

        Schema::dropIfExists('account_carddav_credentials');
        Schema::dropIfExists('space_carddav_servers');
    }
};
