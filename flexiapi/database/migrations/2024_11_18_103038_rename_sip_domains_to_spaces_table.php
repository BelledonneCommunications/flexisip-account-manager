<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Space;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('sip_domains', 'spaces');

        Schema::table('spaces', function (Blueprint $table) {
            $table->string('host')->unique()->nullable();
            $table->integer('max_accounts')->default(0);
            $table->datetime('expire_at')->nullable();
        });

        DB::statement("update spaces set host = domain");

        Schema::table('spaces', function (Blueprint $table) {
            $table->string('host')->nullable(false)->change();
        });

    }

    public function down(): void
    {
        Schema::rename('spaces', 'sip_domains');
        Schema::table('sip_domains', function (Blueprint $table) {
            $table->dropColumn('host');
            $table->dropColumn('max_accounts');
            $table->dropColumn('expire_at');
        });
    }
};
