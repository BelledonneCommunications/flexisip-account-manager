<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

use App\Account;
use App\ProvisioningToken;
use App\RecoveryCode;

return new class extends Migration
{
    public function up()
    {
        Schema::table('phone_change_codes', function (Blueprint $table) {
            $table->string('code')->nullable(true)->change();
        });

        Schema::table('email_change_codes', function (Blueprint $table) {
            $table->string('code')->nullable(true)->change();
        });

        // Move the provisioning tokens and recovery code to a dedicated table
        Schema::create('provisioning_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id')->unsigned();
            $table->string('token')->nullable();
            $table->boolean('used')->default(false);
            $table->timestamps();

            $table->foreign('account_id')->references('id')
                  ->on('accounts')->onDelete('cascade');
        });

        Schema::create('recovery_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id')->unsigned();
            $table->string('code')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')
                  ->on('accounts')->onDelete('cascade');
        });

        // Using a Query Builder as we don't want to use Eloquent magic there
        $accounts = DB::table('accounts')->whereNotNull('provisioning_token')->orWhereNotNull('recovery_code')->get();

        if (DB::getDriverName() !== 'sqlite') {
            $progress = new ProgressBar(new ConsoleOutput, $accounts->count());
            $progress->start();
        }

        foreach ($accounts as $account) {
            if ($account->provisioning_token) {
                $provisioningToken = new ProvisioningToken;
                $provisioningToken->token = $account->provisioning_token;
                $provisioningToken->account_id = $account->id;
                $provisioningToken->save();
            }

            if ($account->recovery_code) {
                $recoveryCode = new RecoveryCode;
                $recoveryCode->code = $account->recovery_code;
                $recoveryCode->account_id = $account->id;
                $recoveryCode->save();
            }

            if (DB::getDriverName() !== 'sqlite') $progress->advance();
        }

        if (DB::getDriverName() !== 'sqlite') $progress->finish();

        // In two steps for SQLite
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('provisioning_token');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('recovery_code');
        });
    }

    public function down()
    {
        Schema::table('phone_change_codes', function (Blueprint $table) {
            $table->string('code')->nullable(false)->change();
        });

        Schema::table('email_change_codes', function (Blueprint $table) {
            $table->string('code')->nullable(false)->change();
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->string('provisioning_token')->nullable();
            $table->string('recovery_code')->nullable();
        });

        // Provisioning tokens
        $provisioningTokens = ProvisioningToken::all();

        if (DB::getDriverName() !== 'sqlite') {
            $progress = new ProgressBar(new ConsoleOutput, $provisioningTokens->count());
            $progress->start();
        }

        foreach ($provisioningTokens as $provisioningToken) {
            $account = Account::where('id', $provisioningToken->account_id)->first();
            $account->provisioning_token = $provisioningToken->token;
            $account->save();

            if (DB::getDriverName() !== 'sqlite') $progress->advance();
        }

        if (DB::getDriverName() !== 'sqlite') $progress->finish();

        // Recovery codes
        $recoveryCodes = RecoveryCode::all();

        if (DB::getDriverName() !== 'sqlite') {
            $progress = new ProgressBar(new ConsoleOutput, $recoveryCodes->count());
            $progress->start();
        }

        foreach ($recoveryCodes as $recoveryCode) {
            $account = Account::where('id', $recoveryCode->account_id)->first();
            $account->recovery_code = $recoveryCode->code;
            $account->save();

            if (DB::getDriverName() !== 'sqlite') $progress->advance();
        }

        if (DB::getDriverName() !== 'sqlite') $progress->finish();

        Schema::dropIfExists('provisioning_tokens');
        Schema::dropIfExists('recovery_codes');
    }
};
