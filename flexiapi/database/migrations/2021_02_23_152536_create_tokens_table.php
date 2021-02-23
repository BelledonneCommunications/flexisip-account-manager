<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTokensTable extends Migration
{
    public function up()
    {
        Schema::connection('local')->create('tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token');
            $table->string('pn_provider');
            $table->string('pn_param');
            $table->string('pn_prid');
            $table->boolean('used')->default(false);
            $table->timestamps();

            $table->index('token');
            $table->index(['pn_provider', 'pn_param', 'pn_prid']);
        });
    }

    public function down()
    {
        Schema::connection('local')->dropIfExists('tokens');
    }
}
