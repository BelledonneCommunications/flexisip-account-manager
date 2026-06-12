<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('username', 64);
            $table->string('strategy')->default('simultaneous');

            $table->bigInteger('call_forwarding_id')->unsigned()->nullable();
            $table->foreign('call_forwarding_id')->references('id')->on('call_forwardings');

            $table->bigInteger('space_id')->unsigned();
            $table->foreign('space_id')->references('id')
                    ->on('spaces')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
