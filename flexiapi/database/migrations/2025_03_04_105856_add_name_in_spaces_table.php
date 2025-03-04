<?php

use App\Space;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->string('name')->nullable();
        });

        DB::statement("update spaces set name = domain");

        Schema::table('spaces', function (Blueprint $table) {
            $table->string('name')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
