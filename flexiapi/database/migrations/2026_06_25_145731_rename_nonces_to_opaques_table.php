<?php

use App\Opaque;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::rename('nonces', 'opaques');

        Opaque::truncate();
        Schema::table('opaques', function (Blueprint $table) {
            if (DB::getDriverName() === 'sqlite') {
                $table->string('ip')->nullable();
                $table->string('opaque', 64)->nullable();
            } else {
                $table->string('ip');
                $table->string('opaque', 64);
            }
        });
    }

    public function down(): void
    {
        Schema::table('opaques', function (Blueprint $table) {
            $table->dropColumn('ip');
            $table->dropColumn('opaque');
        });
        Schema::rename('opaques', 'nonces');
    }
};
