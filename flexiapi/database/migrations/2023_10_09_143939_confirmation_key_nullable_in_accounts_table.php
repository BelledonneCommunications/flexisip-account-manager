<?php

use App\Account;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Account::withoutGlobalScopes()->where('confirmation_key', 'ERROR')->update(['confirmation_key' => null]);
    }

    public function down()
    {
    }
};
