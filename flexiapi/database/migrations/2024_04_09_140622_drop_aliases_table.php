<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('phone', 64)->nullable();
        });

        DB::table('accounts')->update([
            'phone' => DB::raw('(select alias from (select * from aliases where id in (select min(id) from aliases group by account_id)) as a where a.account_id = accounts.id)')
        ]);

        Schema::dropIfExists('aliases');
    }

    public function down()
    {
        Schema::create('aliases', function (Blueprint $table) {
            $table->id();

            $table->integer('account_id')->unsigned();
            $table->string('alias', 64);
            $table->string('domain', 64);

            $table->foreign('account_id')->references('id')
                  ->on('accounts')->onDelete('cascade');

            $table->unique(['alias', 'domain']);
        });

        DB::table('aliases')
            ->insertUsing(
                ['account_id','alias', 'domain'],
                DB::table('accounts')
                    ->select('id', 'phone', 'domain')
                    ->whereNotNull('phone')
            );

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
};
