<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('account_group', function (Blueprint $table) {
            $table->bigInteger('group_id')->unsigned();
            $table->Integer('account_id')->unsigned();

            $table->primary(['group_id', 'account_id']);

            $table->foreign('group_id')->references('id')
                    ->on('groups')->onDelete('cascade');

            $table->foreign('account_id')->references('id')
                    ->on('accounts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_group');
    }
};
