<?php

use App\SipDomain;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sip_domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 64)->unique()->index();
            $table->boolean('super')->default(false);
            $table->timestamps();
        });

        foreach (DB::table('accounts')->select('domain')->distinct()->get()->pluck('domain') as $domain) {
            $sipDomain = new SipDomain;
            $sipDomain->domain = $domain;
            $sipDomain->super = env('APP_ADMINS_MANAGE_MULTI_DOMAINS', false); // historical environnement boolean
            $sipDomain->save();
        }

        Schema::table('accounts', function (Blueprint $table) {
            $table->foreign('domain')->references('domain')
                    ->on('sip_domains')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign('accounts_domain_foreign');
        });
        Schema::dropIfExists('sip_domains');
    }
};
