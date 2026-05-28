<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->boolean('unique_email')->default(true)->after('name');
        });

        DB::table('spaces')->update([
            'unique_email' => (bool) env('ACCOUNT_EMAIL_UNIQUE', true)
        ]);
    }

    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn('unique_email');
        });
    }
};
