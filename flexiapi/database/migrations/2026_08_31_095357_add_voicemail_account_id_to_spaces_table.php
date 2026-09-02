<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->integer('voicemail_account_id')->unsigned()->nullable();
            $table->foreign('voicemail_account_id')->references('id')
                ->on('accounts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropForeign('spaces_voicemail_account_id_foreign');
            $table->dropColumn('voicemail_account_id');
        });
    }
};
