<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2023 Belledonne Communications SARL, All rights reserved.

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

namespace App\Http\Controllers\Admin;

use App\Account;
use App\ExternalAccount;
use App\Password;
use App\PhoneCountry;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Propaganistas\LaravelPhone\PhoneNumber;

class AccountImportController extends Controller
{
    private Collection $errors;
    private string $importDirectory = 'imported_csv';

    public function __construct()
    {
        $this->errors = collect();
    }

    public function create(Request $request)
    {
        return view('admin.account.import.create', [
            'domains' => Account::select('domain')->distinct()->get()->pluck('domain')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'csv' => ['required', File::types(['csv', 'txt'])],
            'domain' => 'required|exists:accounts'
        ]);

        $domain = $request->get('domain');

        /**
         * General formating checking
         */
        $csv = fopen($request->file('csv'), 'r');
        $line = fgets($csv);
        fclose($csv);

        $lines = collect();
        $this->errors['Wrong file format'] = "The number of columns doesn't matches the reference file. The first MUST be the same as the reference file";

        if ($line == "Username,Password,Role,Status,Phone,Email,External Username,External Domain,External Password,External Realm, External Registrar,External Outbound Proxy,External Protocol\n") {
            $lines = $this->csvToCollection($request->file('csv'));
            unset($this->errors['Wrong file format']);
        }

        /**
         * Error checking
         */

        // Usernames

        $existingUsernames = Account::where('domain', $domain)
            ->whereIn('username', $lines->pluck('username')->all())
            ->pluck('username');

        if ($existingUsernames->isNotEmpty()) {
            $this->errors['Those usernames already exists'] = $existingUsernames->join(', ', ' and ');
        }

        if ($duplicates = $lines->pluck('username')->duplicates()) {
            if ($duplicates->isNotEmpty()) {
                $this->errors['Those usernames are declared several times'] = $duplicates->join(', ', ' and ');
            }
        }

        if ($lines->pluck('username')->contains(function ($value) {
            return strlen($value) < 2;
        })) {
            $this->errors['Some usernames are shorter than expected'] = '';
        }

        // Passwords

        if ($lines->pluck('password')->contains(function ($value) {
            return strlen($value) < 6;
        })) {
            $this->errors['Some passwords are shorter than expected'] = '';
        }

        // Roles

        if ($lines->pluck('role')->contains(function ($value) {
            return !in_array($value, ['admin', 'user']);
        })) {
            $this->errors['Some roles are not correct'] = '';
        }

        // Status

        if ($lines->pluck('status')->contains(function ($value) {
            return !in_array($value, ['active', 'inactive']);
        })) {
            $this->errors['Some statuses are not correct'] = '';
        }

        // Phones

        $phoneCountries = PhoneCountry::where('activated', true)->get();

        if ($phones = $lines->pluck('phone')->filter(function ($value) {
            return !empty($value);
        })->filter(function ($value) use ($phoneCountries) {
            return !$phoneCountries->firstWhere('code', (new PhoneNumber($value))->getCountry());
        })) {
            if ($phones->isNotEmpty()) {
                $this->errors['Some phone numbers are not correct'] = $phones->join(', ', ' and ');
            }
        }

        $existingPhones = Account::whereIn('phone', $lines->pluck('phone')->all())
            ->pluck('phone');

        if ($existingPhones->isNotEmpty()) {
            $this->errors['Those phones numbers already exists'] = $existingPhones->join(', ', ' and ');
        }

        // Emails

        if ($emails = $lines->pluck('email')->filter(function ($value) {
            return !filter_var($value, FILTER_VALIDATE_EMAIL);
        })) {
            if ($emails->isNotEmpty()) {
                $this->errors['Some emails are not correct'] = $emails->join(', ', ' and ');
            }
        }

        $existingEmails = Account::whereIn('email', $lines->pluck('email')->all())
            ->pluck('email');

        if ($existingEmails->isNotEmpty()) {
            $this->errors['Those emails numbers already exists'] = $existingEmails->join(', ', ' and ');
        }

        if ($emails = $lines->pluck('email')->duplicates()) {
            if ($emails->isNotEmpty()) {
                $this->errors['Those emails are declared several times'] = $emails->join(', ', ' and ');
            }
        }

        // External account

        foreach ($lines as $line) {
            if ($line->external_username != null && ($line->external_password == null || $line->external_domain == null)) {
                $this->errors['Line ' . $line->line . ': The mandatory external account columns must be filled'] = '';
            }

            if ($line->external_username != null && $line->external_password != null && $line->external_domain != null) {
                if ($line->external_domain == $line->external_realm
                 || $line->external_domain == $line->external_registrar
                 || $line->external_domain == $line->external_outbound_proxy) {
                    $this->errors['Line ' . $line->line . ': External realm, registrar or outbound proxy must be different than domain'] = '';
                }

                if (!in_array($line->external_protocol, ExternalAccount::PROTOCOLS)) {
                    $this->errors['Line ' . $line->line . ': External protocol must be UDP, TCP or TLS'] = '';
                }
            }
        }

        $filePath = $this->errors->isEmpty()
            ? Storage::putFile($this->importDirectory, $request->file('csv'))
            : null;

        return view('admin.account.import.check', [
            'linesCount' => $lines->count(),
            'errors' => $this->errors,
            'domain' => $request->get('domain'),
            'filePath' => $filePath
        ]);
    }

    public function handle(Request $request)
    {
        $request->validate([
            'file_path' => 'required',
            'domain' => 'required|exists:accounts'
        ]);

        $lines = $this->csvToCollection(storage_path('app/' . $request->get('file_path')));

        $accounts = [];
        $now = \Carbon\Carbon::now();

        $admins = $phones = $passwords = $externals = [];

        $externalAlgorithm = 'MD5';

        foreach ($lines as $line) {
            if ($line->role == 'admin') {
                array_push($admins, $line->username);
            }

            if (!empty($line->phone)) {
                $phones[$line->username] = $line->phone;
            }

            if (!empty($line->password)) {
                $passwords[$line->username] = $line->password;
            }

            if (!empty($line->external_username)) {
                $externals[$line->username] = [
                    'username' => $line->external_username,
                    'domain' => $line->external_domain,
                    'realm' => $line->external_realm,
                    'registrar' => $line->external_registrar,
                    'outbound_proxy' => $line->external_outbound_proxy,
                    'protocol' => $line->external_protocol,
                    'password' => bchash(
                        $line->external_username,
                        $line->external_realm ?? $line->external_domain,
                        $line->external_password,
                        $externalAlgorithm
                    ),
                    'algorithm' => $externalAlgorithm,
                    'created_at' => Carbon::now(),
                ];
            }

            array_push($accounts, [
                'username' => $line->username,
                'domain' => $request->get('domain'),
                'email' => $line->email,
                'activated' => $line->status == 'active',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'CSV Import',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Account::insert($accounts);

        // Set admins accounts
        foreach ($admins as $username) {
            $account = Account::where('username', $username)
                ->where('domain', $request->get('domain'))
                ->first();
            $account->admin = true;
        }

        // Set passwords

        $passwordsToInsert = [];

        $passwordAccounts = Account::whereIn('username', array_keys($passwords))
            ->where('domain', $request->get('domain'))
            ->get();

        $algorithm = config('app.account_default_password_algorithm');

        foreach ($passwordAccounts as $passwordAccount) {
            array_push($passwordsToInsert, [
                'account_id' => $passwordAccount->id,
                'password' => bchash(
                    $passwordAccount->username,
                    space()?->account_realm ?? $request->get('domain'),
                    $passwords[$passwordAccount->username],
                    $algorithm
                ),
                'algorithm' => $algorithm
            ]);
        }

        Password::insert($passwordsToInsert);

        // Set external account

        $externalAccountsToInsert = [];

        $externalAccounts = Account::whereIn('username', array_keys($externals))
            ->where('domain', $request->get('domain'))
            ->get();

        foreach ($externalAccounts as $externalAccount) {
            array_push($externalAccountsToInsert, [
                'account_id' => $externalAccount->id
            ] + $externals[$externalAccount->username]);
        }

        ExternalAccount::insert($externalAccountsToInsert);

        // Set phone accounts
        foreach ($phones as $username => $phone) {
            $account = Account::where('username', $username)
                ->where('domain', $request->get('domain'))
                ->first();
            $account->phone = $phone;
        }

        return redirect()->route('admin.account.index');
    }

    private function csvToCollection($file): Collection
    {
        $lines = collect();
        $csv = fopen($file, 'r');

        $i = 1;
        while (!feof($csv)) {
            if ($line = fgetcsv($csv, 1000, ',')) {
                if (count($line) != 13) {
                    $this->errors['Parsing error at line ' . $i] = 'The number of columns is incorrect';
                    $i++;
                    continue;
                }

                $lines->push((object)[
                    'line' => $i,
                    'username' => !empty($line[0]) ? $line[0] : null,
                    'password' => !empty($line[1]) ? $line[1] : null,
                    'role' => $line[2],
                    'status' => $line[3],
                    'phone' => !empty($line[4]) ? $line[4] : null,
                    'email' => $line[5],
                    'external_username' => !empty($line[6]) ? $line[6] : null,
                    'external_domain' => !empty($line[7]) ? $line[7] : null,
                    'external_password' => !empty($line[8]) ? $line[8] : null,
                    'external_realm' => !empty($line[9]) ? $line[9] : null,
                    'external_registrar' => !empty($line[10]) ? $line[10] : null,
                    'external_outbound_proxy' => !empty($line[11]) ? $line[11] : null,
                    'external_protocol' => $line[12],
                ]);

                $i++;
            }
        }

        fclose($csv);

        $lines->shift();

        return $lines;
    }
}
