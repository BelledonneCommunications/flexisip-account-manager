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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use Illuminate\Support\Facades\DB;

function passwordAlgorithms(): array
{
    return [
        'MD5'     => 'md5',
        'SHA-256' => 'sha256',
    ];
}

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
    return hash(passwordAlgorithms()[$algorithm], $username . ':' . $domain . ':' . $password);
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

function captchaConfigured(): bool
{
    return env('NOCAPTCHA_SECRET', false) != false || env('NOCAPTCHA_SITEKEY', false) != false;
}

function resolveUserContacts(Request $request)
{
    $selected = ['id', 'username', 'domain', 'activated', 'dtmf_protocol'];

    return Account::whereIn('id', function ($query) use ($request) {
        $query->select('contact_id')
            ->from('contacts')
            ->where('account_id', $request->user()->id)
            ->union(
                DB::table('contacts_list_contact')
                    ->select('contact_id')
                    ->whereIn('contacts_list_id', function ($query) use ($request) {
                        $query->select('contacts_list_id')
                            ->from('account_contacts_list')
                            ->where('account_id', $request->user()->id);
                    })
            );
    })->select($selected);
}

/**
 * Validate date string to ISO8601
 * From: https://github.com/penance316/laravel-iso8601-validator/blob/master/src/IsoDateValidator.php
 *
 * @param $attribute
 * @param $value
 * @param $parameters
 * @param $validator
 *
 * @return bool
 */
function validateIsoDate($attribute, $value, $parameters, $validator): bool
{
    $regex = (is_array($parameters) && in_array('utc', $parameters))
        ? '/^(\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)))?$/'
        // 2012-04-23T18:25:43.511Z
        // Regex from https://www.myintervals.com/blog/2009/05/20/iso-8601-date-validation-that-doesnt-suck/
        : '/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/';

    return (bool)preg_match($regex, $value);
}
