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

class AddUniqueAccountsPasswordsAliasesTable extends Migration
{
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->unique(['username', 'domain']);
        });

        Schema::table('passwords', function (Blueprint $table) {
            $table->unique(['account_id', 'algorithm']);
        });

        Schema::table('aliases', function (Blueprint $table) {
            $table->unique(['alias', 'domain']);
        });
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropUnique(['username', 'domain']);
        });

        // Some versions of MySQL have a weird behavior between their FK and indexes
        // See https://stackoverflow.com/questions/8482346/mysql-cannot-drop-index-needed-in-a-foreign-key-constraint
        // We need to drop the foreign key to recreate it just after...

        Schema::table('passwords', function (Blueprint $table) {
            $table->dropForeign('passwords_account_id_foreign');
            $table->dropUnique(['account_id', 'algorithm']);
        });

        Schema::table('passwords', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')
                ->on('accounts')->onDelete('cascade');
        });

        Schema::table('aliases', function (Blueprint $table) {
            $table->dropUnique(['alias', 'domain']);
        });

        Schema::enableForeignKeyConstraints();
    }
}
