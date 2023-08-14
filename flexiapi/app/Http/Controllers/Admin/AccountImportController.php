<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Alias;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

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

        $lines = $this->csvToCollection($request->file('csv'));

        /**
         * Error checking
         */

        // Usernames

        $existingUsernames = Account::where('domain', $request->get('domain'))
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
            $this->errors['Some status are not correct'] = '';
        }

        // Phones

        if ($phones = $lines->pluck('phone')->filter(function ($value) {
            return strlen($value) > 2 && substr($value, 0, 1) != '+';
        })) {
            if ($phones->isNotEmpty()) {
                $this->errors['Some phone numbers are not correct'] = $phones->join(', ', ' and ');
            }
        }

        $existingPhones = Alias::whereIn('alias', $lines->pluck('phone')->all())
            ->pluck('alias');

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

        $admins = $phones = [];

        foreach ($lines as $line) {
            if ($line->role == 'admin') {
                array_push($admins, $line->username);
            }

            if (!empty($line->phone)) {
                $phones[$line->username] = $line->phone;
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

        // Set admins accounts
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
                $lines->push((object)[
                    'line' => $i,
                    'username' => $line[0],
                    'password' => $line[1],
                    'role' => $line[2],
                    'status' => $line[3],
                    'phone' => $line[4],
                    'email' => $line[5],
                ]);

                $i++;
            }
        }

        fclose($csv);

        $lines->shift();

        return $lines;
    }
}
