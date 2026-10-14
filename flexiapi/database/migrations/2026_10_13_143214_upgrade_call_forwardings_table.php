<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('call_forwardings', function (Blueprint $table) {
            $table->integer('voicemail_account_id')->unsigned()->nullable();
            $table->foreign('voicemail_account_id')->references('id')
                ->on('accounts')->onDelete('cascade');
            $table->integer('sip_account_id')->unsigned()->nullable();
            $table->foreign('sip_account_id')->references('id')
                ->on('accounts')->onDelete('cascade');
            $table->renameColumn('contact_id', 'contact_account_id');
            $table->dropColumn('forward_to');
            $table->dropColumn('sip_uri');
        });
    }

    public function down(): void
    {
        Schema::table('call_forwardings', function (Blueprint $table) {
            $table->dropForeign('call_forwardings_voicemail_account_id_foreign');
            $table->dropColumn('voicemail_account_id');
            $table->dropForeign('call_forwardings_sip_account_id_foreign');
            $table->dropColumn('sip_account_id');
            $table->renameColumn('contact_account_id', 'contact_id');
            $table->string('forward_to');
            $table->string('sip_uri')->nullable();
        });
    }
};
