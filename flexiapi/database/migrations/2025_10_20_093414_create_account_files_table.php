<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('account_id')->unsigned()->nullable();
            $table->foreign('account_id')->references('id')
                ->on('accounts')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->integer('size')->nullable();
            $table->string('content_type')->index();
            $table->text('sip_from')->nullable();
            $table->dateTime('uploaded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_files');
    }
};
