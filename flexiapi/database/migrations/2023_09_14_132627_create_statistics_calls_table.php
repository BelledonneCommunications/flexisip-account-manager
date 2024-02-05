<?php

use App\StatisticsMessage;
use App\StatisticsMessageDevice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('statistics_calls', function (Blueprint $table) {
            $table->string('id', 64)->unique();
            $table->string('from_username', 256);
            $table->string('from_domain', 256);
            $table->string('to_username', 256);
            $table->string('to_domain', 256);
            $table->dateTime('initiated_at');
            $table->dateTime('ended_at')->nullable();
            $table->string('conference_id')->nullable();
            $table->timestamps();

            $table->index(['from_username', 'from_domain']);
            $table->index('initiated_at');
        });

        Schema::create('statistics_call_devices', function (Blueprint $table) {
            $table->id();
            $table->string('call_id', 64);
            $table->string('device_id', 64);
            $table->dateTime('rang_at')->nullable();
            $table->dateTime('invite_terminated_at')->nullable();
            $table->string('invite_terminated_state')->nullable();
            $table->timestamps();

            $table->foreign('call_id')->references('id')->on('statistics_calls')->onDelete('cascade');
            $table->unique(['call_id', 'device_id']);
        });

        Schema::disableForeignKeyConstraints();
        Schema::drop('statistics_message_devices');
        Schema::drop('statistics_messages');

        Schema::create('statistics_messages', function (Blueprint $table) {
            $table->string('id', 64)->unique();
            $table->string('from_username', 256);
            $table->string('from_domain', 256);
            $table->dateTime('sent_at');
            $table->boolean('encrypted')->default(false);
            $table->string('conference_id')->nullable();
            $table->timestamps();

            $table->index(['from_username', 'from_domain']);
            $table->index('sent_at');
        });

        Schema::create('statistics_message_devices', function (Blueprint $table) {
            $table->id();
            $table->string('message_id', 64);
            $table->string('to_username', 256);
            $table->string('to_domain', 256);
            $table->string('device_id', 64);
            $table->integer('last_status');
            $table->dateTime('received_at');
            $table->timestamps();

            $table->foreign('message_id')->references('id')->on('statistics_messages')->onDelete('cascade');
            $table->unique(['message_id', 'to_username', 'to_domain', 'device_id'], 'statistics_message_devices_message_id_to_u_to_d_device_id_unique');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('statistics_calls');
        Schema::dropIfExists('statistics_call_devices');

        StatisticsMessageDevice::truncate();
        StatisticsMessage::truncate();

        Schema::table('statistics_messages', function(Blueprint $table) {
            $table->dropIndex('statistics_messages_from_username_from_domain_index');
            $table->dropColumn('from_username');
            $table->dropColumn('from_domain');

            $table->string('from', 256)->index();
        });

        Schema::table('statistics_message_devices', function(Blueprint $table) {
            $table->dropForeign('statistics_message_devices_message_id_foreign');
            $table->dropUnique('statistics_message_devices_message_id_to_u_to_d_device_id_unique');
            $table->dropColumn('to_username');
            $table->dropColumn('to_domain');

            $table->string('to', 256);

            $table->unique(['message_id', 'to', 'device_id']);
        });

        Schema::enableForeignKeyConstraints();
    }
};
