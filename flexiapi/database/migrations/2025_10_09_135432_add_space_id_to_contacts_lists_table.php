<?php

use App\Space;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        /**
         * We have to make a choice to run the migration ¯\_(ツ)_/¯
         */
        $space = (Space::count() == 1)
            ? Space::first()
            : Space::where('super', true)->first();

        Schema::table('contacts_lists', function (Blueprint $table) use ($space) {
            if ($space) {
                $table->bigInteger('space_id')->unsigned()->default($space->id);
            } else {
                $table->bigInteger('space_id')->unsigned()->nullable();
            }

            $table->foreign('space_id')->references('id')
                ->on('spaces')->onDelete('cascade');
        });

        Schema::table('contacts_lists', function (Blueprint $table) use ($space) {
            $table->bigInteger('space_id')->unsigned()->change();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::table('contacts_lists', function (Blueprint $table) {
            $table->dropForeign('contacts_lists_space_id_foreign');
            $table->dropColumn('space_id');
        });
    }
};
