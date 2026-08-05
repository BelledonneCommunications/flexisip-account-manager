<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn('provisioning_use_linphone_provisioning_header');
        });
    }

    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->boolean('provisioning_use_linphone_provisioning_header')->default(true);
        });
    }
};
