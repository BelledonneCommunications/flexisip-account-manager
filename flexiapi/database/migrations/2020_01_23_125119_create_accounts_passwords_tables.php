<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2020 Belledonne Communications SARL, All rights reserved.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsPasswordsTables extends Migration
{
    public function up()
    {
        if (!Schema::connection('external')->hasTable('accounts')) {
            Schema::connection('external')->create('accounts', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('username', 64);
                $table->string('domain', 64);
                $table->string('email', 64)->nullable();
                $table->boolean('activated')->default(false);
                $table->string('confirmation_key', 14)->nullable();
                $table->string('ip_address', 39);
                $table->string('user_agent', 256);
                $table->datetime('creation_time');
                $table->datetime('expire_time')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::connection('external')->hasTable('passwords')) {
            Schema::connection('external')->create('passwords', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('account_id')->unsigned();
                $table->string('password', 255);
                $table->string('algorithm', 10)->default('MD5');

                //$table->foreign('account_id')->references('id')
                //      ->on('accounts')->onDelete('cascade');

                $table->timestamps();
            });
        }
    }

    public function down()
    {
        //Schema::connection('external')->dropIfExists('passwords');
        //Schema::connection('external')->dropIfExists('accounts');
    }
}
