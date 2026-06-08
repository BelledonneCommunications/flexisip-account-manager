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

namespace App\Http\Requests\Space;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Account;

class AdministrationUpdate extends FormRequest
{
    public function rules()
    {
        $space = $this->route('space');
        $domain = $space->domain;

        return [
            'name' => ['required', Rule::unique('spaces')->ignore($space->id)],
            'max_accounts' => 'required|integer|min:0',
            'expire_at' => 'nullable|date|after_or_equal:today',
            'unique_email' => [
                function ($attribute, $value, $fail) use ($domain) {
                    $duplicates = $value
                        ? Account::where('domain', $domain)
                            ->whereNotNull('email')
                            ->select('email', DB::raw('count(*) as duplicate_count'))
                            ->groupBy('email')
                            ->having('duplicate_count', '>', 1)
                            ->with([])
                            ->get()
                            ->pluck('email', 'duplicate_count')
                        : [];

                    if ($duplicates->isNotEmpty()) {
                        dd($duplicates);
                        $messages = [];

                        foreach ($duplicates as $key => $value) {
                            $messages[] = "The address {$value} is used by {$key} accounts";
                        }

                        $fail("Action impossible:\n- " . implode("\n- ", $messages));
                    }
                }
            ]
        ];
    }
}
