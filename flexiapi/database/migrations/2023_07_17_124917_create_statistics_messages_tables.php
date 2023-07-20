<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('statistics_messages', function (Blueprint $table) {
            $table->string('id', 64)->unique();
            $table->string('from', 256)->index();
            $table->dateTime('sent_at');
            $table->boolean('encrypted')->default(false);
            $table->string('conference_id')->nullable();
            $table->timestamps();
        });

        Schema::create('statistics_message_devices', function (Blueprint $table) {
            $table->id();
            $table->string('message_id', 64);
            $table->string('to', 256)->index();
            $table->string('device_id', 64);
            $table->integer('last_status');
            $table->dateTime('received_at');
            $table->timestamps();

            $table->foreign('message_id')->references('id')->on('statistics_messages');
            $table->unique(['message_id', 'to', 'device_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('statistics_message_devices');
        Schema::dropIfExists('statistics_messages');
    }
};
