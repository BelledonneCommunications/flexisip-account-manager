<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

use App\ApiKey;

class AddLastUsedAtColumnToApiKeysTable extends Migration
{
    public function up()
    {
        ApiKey::truncate();

        Schema::table('api_keys', function (Blueprint $table) {
            if (DB::getDriverName() == 'sqlite') {
                $table->dateTime('last_used_at')->default('');
            } else {
                $table->dateTime('last_used_at');
            }
        });
    }

    public function down()
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropColumn('last_used_at');
        });
    }
}
