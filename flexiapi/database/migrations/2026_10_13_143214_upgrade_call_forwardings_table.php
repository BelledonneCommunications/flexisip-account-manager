<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // The former forwardings rely on types (busy) and targets (external
        // SIP URIs, plain contacts) that no longer exist in the reworked
        // feature, drop them all
        DB::table('call_forwardings')->delete();

        Schema::table('call_forwardings', function (Blueprint $table) {
            $table->integer('voicemail_account_id')->unsigned()->nullable();
            $table->foreign('voicemail_account_id')->references('id')
                ->on('accounts')->onDelete('cascade');
            $table->integer('sip_account_id')->unsigned()->nullable();
            $table->foreign('sip_account_id')->references('id')
                ->on('accounts')->onDelete('cascade');
        });

        Schema::table('call_forwardings', function (Blueprint $table) {
            $table->dropForeign(['contact_id']);
            $table->dropColumn('contact_id');
            $table->dropColumn('forward_to');
            $table->dropColumn('sip_uri');
        });
    }

    public function down(): void
    {
        // The forwardings created with the reworked feature cannot fit
        // in the former schema, drop them as well
        DB::table('call_forwardings')->delete();

        Schema::table('call_forwardings', function (Blueprint $table) {
            $table->dropForeign('call_forwardings_voicemail_account_id_foreign');
            $table->dropColumn('voicemail_account_id');
            $table->dropForeign('call_forwardings_sip_account_id_foreign');
            $table->dropColumn('sip_account_id');
            $table->integer('contact_id')->unsigned()->nullable();
            $table->foreign('contact_id')->references('id')
                ->on('accounts')->onDelete('cascade');
            $table->string('forward_to');
            $table->string('sip_uri')->nullable();
        });
    }
};
