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

use Illuminate\Support\Str;

use App\Account;
use App\DigestNonce;
use App\ExternalAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;

function generateNonce(): string
{
    return Str::random(32);
}

function generateValidNonce(Account $account): string
{
    $nonce = new DigestNonce;
    $nonce->account_id = $account->id;
    $nonce->nonce = generateNonce();
    $nonce->save();

    return $nonce->nonce;
}

function bchash(string $username, string $domain, string $password, string $algorithm = 'MD5')
{
    $algos = ['MD5' => 'md5', 'SHA-256' => 'sha256'];

    return hash($algos[$algorithm], $username . ':' . $domain . ':' . $password);
}

function generatePin()
{
    return mt_rand(1000, 9999);
}

function percent($value, $max)
{
    if ($max == 0) $max = 1;
    return round(($value * 100) / $max, 2);
}

function markdownDocumentationView($view): string
{
    $converter = new CommonMarkConverter([
        'heading_permalink' => [
            'html_class' => 'permalink',
            'insert' => 'after',
            'title' => 'Permalink',
            'id_prefix' => '',
            'fragment_prefix' => '',
        ],
        'table_of_contents' => [
            'html_class' => 'table-of-contents float-right',
        ],
    ]);

    $converter->getEnvironment()->addExtension(new HeadingPermalinkExtension);
    $converter->getEnvironment()->addExtension(new TableOfContentsExtension);

    return (string) $converter->convert(
        (string)view($view, [
            'app_name' => config('app.name')
        ])->render()
    );
}

function getAvailableExternalAccount(): ?ExternalAccount
{
    if (Schema::hasTable('external_accounts')) {
        return ExternalAccount::where('used', false)
            ->where('account_id', null)
            ->first();
    }

    return null;
}

function publicRegistrationEnabled(): bool
{
    if (config('app.public_registration')) {
        if (config('app.consume_external_account_on_create')) {
            return (bool)getAvailableExternalAccount();
        }

        return true;
    }

    return false;
}

function isRegularExpression($string): bool
{
    set_error_handler(function () {
    }, E_WARNING);

    $isRegularExpression = preg_match($string, '') !== false;
    restore_error_handler();

    return $isRegularExpression;
}

function resolveDomain(Request $request): string
{
    return $request->has('domain')
        && $request->user()
        && $request->user()->admin
        && config('app.admins_manage_multi_domains')
            ? $request->get('domain')
            : config('app.sip_domain');
}
