<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAliasesTable extends Migration
{
    public function up()
    {
        if (!Schema::connection('external')->hasTable('aliases')) {
            Schema::connection('external')->create('aliases', function (Blueprint $table) {
                $table->id();

                $table->integer('account_id')->unsigned();
                $table->string('alias', 64);
                $table->string('domain', 64);

                $table->foreign('account_id')->references('id')
                    ->on('accounts')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        //Schema::dropIfExists('aliases');
    }
}
