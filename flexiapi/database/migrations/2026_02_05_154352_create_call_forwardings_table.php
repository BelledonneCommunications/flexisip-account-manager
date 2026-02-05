<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('call_forwardings', function (Blueprint $table) {
            $table->id();
            $table->integer('account_id')->unsigned()->nullable();
            $table->foreign('account_id')->references('id')
                ->on('accounts')->onDelete('cascade');
            $table->string('type');
            $table->string('forward_to');
            $table->string('sip_uri')->nullable();
            $table->boolean('enabled')->default(false);
            $table->integer('contact_id')->unsigned()->nullable();
            $table->foreign('contact_id')->references('id')
                ->on('accounts')->onDelete('cascade');
            $table->unique(['account_id', 'type']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_forwardings');
    }
};
