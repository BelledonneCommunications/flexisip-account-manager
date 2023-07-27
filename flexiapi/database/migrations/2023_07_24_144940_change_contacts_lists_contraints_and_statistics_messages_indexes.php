<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        DB::query('delete from contacts_lists');
        Schema::enableForeignKeyConstraints();

        Schema::table('contacts_lists', function (Blueprint $table) {
            $table->unique('title');
            $table->text('description')->nullable(true)->change();
        });

        Schema::table('statistics_messages', function (Blueprint $table) {
            $table->index('sent_at');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::table('contacts_lists', function (Blueprint $table) {
            $table->dropUnique('contacts_lists_title_unique');
            $table->text('description')->nullable(false)->change();
        });

        Schema::table('statistics_messages', function (Blueprint $table) {
            $table->dropIndex('statistics_messages_sent_at_index');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropIndex('accounts_created_at_index');
        });
    }
};
