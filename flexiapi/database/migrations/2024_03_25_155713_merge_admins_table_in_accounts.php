<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->boolean('admin')->default(false);
        });

        DB::table('accounts')->whereIn('id', function($query){
            $query->select('account_id')
                  ->from('admins');
        })->update(['admin' => true]);

        Schema::dropIfExists('admins');
    }

    public function down()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id')->unsigned();
            $table->timestamps();

            $table->foreign('account_id')->references('id')
                  ->on('accounts')->onDelete('cascade');
        });

        foreach (DB::table('accounts')->where('admin', true)->get(['id']) as $account) {
            DB::table('admins')->insert([
                'account_id' => (string)$account->id,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
        }

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('admin');
        });
    }
};
