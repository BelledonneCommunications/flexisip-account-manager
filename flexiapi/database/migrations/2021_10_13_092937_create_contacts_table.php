<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateContactsTable extends Migration
{
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('display_name')->nullable();
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->integer('account_id')->unsigned();
            $table->integer('contact_id')->unsigned();
            $table->foreign('account_id')->references('id')
                  ->on('accounts')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')
                  ->on('accounts')->onDelete('cascade');
            $table->unique(['account_id', 'contact_id']);
            $table->timestamps();
        });

        Schema::create('account_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key');
            $table->timestamps();
        });

        Schema::create('account_account_type', function (Blueprint $table) {
            $table->integer('account_id')->unsigned();
            $table->integer('account_type_id')->unsigned();
            $table->foreign('account_id')->references('id')
                  ->on('accounts')->onDelete('cascade');
            $table->foreign('account_type_id')->references('id')
                  ->on('account_types')->onDelete('cascade');
            $table->unique(['account_id', 'account_type_id']);
            $table->timestamps();
        });

        Schema::create('account_actions', function (Blueprint $table) {
            $table->id();
            $table->integer('account_id')->unsigned();
            $table->foreign('account_id')->references('id')
                  ->on('accounts')->onDelete('cascade');
            $table->string('key');
            $table->string('code');

            /**
             * See 2022_01_19_160606_move_protocol_from_account_actions_to_account.php
             * SQLite can't handle the migration in the testing pipeline, so we must
             * prevent the column to be created in the first place
             **/
            if (DB::getDriverName() !== 'sqlite') {
                $table->string('protocol');
            }
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('display_name');
        });

        Schema::dropIfExists('contacts');
        Schema::dropIfExists('account_types');
        Schema::dropIfExists('account_account_type');
        Schema::dropIfExists('account_actions');

        Schema::enableForeignKeyConstraints();
    }
}
