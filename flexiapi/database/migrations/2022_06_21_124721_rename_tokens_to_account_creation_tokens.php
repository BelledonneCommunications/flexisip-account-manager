<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameTokensToAccountCreationTokens extends Migration
{
    public function up()
    {
        Schema::rename('tokens', 'account_creation_tokens');

        Schema::table('account_creation_tokens', function (Blueprint $table) {
            $table->integer('account_id')->unsigned()->nullable();
            $table->foreign('account_id')->references('id')
                  ->on('accounts')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('account_creation_tokens', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });

        Schema::rename('account_creation_tokens', 'tokens');

        Schema::enableForeignKeyConstraints();
    }
}
