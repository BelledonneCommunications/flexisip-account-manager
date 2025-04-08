<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('space_email_servers', function (Blueprint $table) {
            $table->id();

            $table->string('host', 64);
            $table->integer('port');
            $table->string('username', 128)->nullable();
            $table->string('password', 128)->nullable();
            $table->string('from_address', 128)->nullable();
            $table->string('from_name', 128)->nullable();
            $table->string('signature', 256)->nullable();

            $table->bigInteger('space_id')->unsigned();
            $table->foreign('space_id')->references('id')
                    ->on('spaces')->onDelete('cascade');

            $table->unique('space_id');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('space_email_servers');
    }
};
