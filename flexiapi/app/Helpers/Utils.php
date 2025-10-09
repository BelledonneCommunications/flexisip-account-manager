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

use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

use App\Account;
use App\Space;
use App\DigestNonce;
use Illuminate\Http\Request;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use Illuminate\Support\Facades\DB;

function space(): ?Space
{
    return is_object(request()->space) ? request()->space : null;
}

function passwordAlgorithms(): array
{
    return [
        'MD5' => 'md5',
        'SHA-256' => 'sha256',
    ];
}

function generateNonce(): string
{
    return Str::random(32);
}

function getRequestBoolean(Request $request, string $key, bool $reversed = false): bool
{
    $bool = $request->has($key) ? $request->get($key) == "on" : false;

    return $reversed ? !$bool : $bool;
}

function generateValidNonce(Account $account): string
{
    $nonce = new DigestNonce();
    $nonce->account_id = $account->id;
    $nonce->nonce = generateNonce();
    $nonce->save();

    return $nonce->nonce;
}

function bchash(string $username, string $domain, string $password, string $algorithm = 'MD5'): string
{
    return hash(passwordAlgorithms()[$algorithm], $username . ':' . $domain . ':' . $password);
}

function generatePin(): int
{
    return mt_rand(1000, 9999);
}

function percent($value, $max): float
{
    if ($max == 0) {
        $max = 1;
    }
    return round(($value * 100) / $max, 2);
}

function markdownDocumentationView(string $view): string
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

    $converter->getEnvironment()->addExtension(new HeadingPermalinkExtension());
    $converter->getEnvironment()->addExtension(new TableOfContentsExtension());

    return (string) $converter->convert(
        (string) view($view, [
            'app_name' => space()->name
        ])->render()
    );
}

function hasCoTURNConfigured(): bool
{
    return config('app.coturn_session_ttl_minutes') > 0
        && !empty(config('app.coturn_server_host'))
        && !empty(config('app.coturn_static_auth_secret'));
}

function getCoTURNCredentials(): array
{
    $user = Str::random(8);
    $secret = config('app.coturn_static_auth_secret');

    $ttl = config('app.coturn_session_ttl_minutes') * 60;
    $time = time() + $ttl;

    $username = $time . ':' . Str::random(16);
    $password = base64_encode(hash_hmac('sha1', $username, $secret, true));

    return [
        'username' => $username,
        'password' => $password,
    ];
}

function parseSIP(string $sipAdress): array
{
    return explode('@', \substr($sipAdress, 4));
}

function isRegularExpression(string $string): bool
{
    set_error_handler(function () {
    }, E_WARNING);

    $isRegularExpression = preg_match($string, '') !== false;
    restore_error_handler();

    return $isRegularExpression;
}

function replaceHost(string $url, string $host): string
{
    $components = parse_url($url);
    return str_replace($components['host'], $host, $url);
}

function resolveDomain(Request $request): string
{
    return $request->has('domain')
        && $request->user()
        && $request->user()->superAdmin
        ? $request->get('domain')
        : $request->space->domain;
}

function maxUploadSize(): ?int
{
    $uploadMaxSizeInBytes = ini_parse_quantity(ini_get('upload_max_filesize'));
    if ($uploadMaxSizeInBytes > 0) {
        return $uploadMaxSizeInBytes / 1024;
    }

    $postMaxSizeInBytes = ini_parse_quantity(ini_get('post_max_size'));
    if ($postMaxSizeInBytes > 0) {
        return $postMaxSizeInBytes / 1024;
    }
}

function captchaConfigured(): bool
{
    return env('HCAPTCHA_SECRET', false) != false || env('HCAPTCHA_SITEKEY', false) != false;
}

function resolveUserContacts(Request $request)
{
    $selected = ['id', 'username', 'domain', 'activated', 'dtmf_protocol', 'display_name'];

    return Account::withoutGlobalScopes()->whereIn('id', function ($query) use ($request) {
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

    return (bool) preg_match($regex, $value);
}

/**
 * This list was got from the Internet
 *
 * @see https://gist.github.com/vxnick/380904
 * @return array
 */
function getCountryCodes()
{
    return [
        'AD' => 'Andorra',
        'AE' => 'United Arab Emirates',
        'AF' => 'Afghanistan',
        'AG' => 'Antigua & Barbuda',
        'AI' => 'Anguilla',
        'AL' => 'Albania',
        'AM' => 'Armenia',
        'AO' => 'Angola',
        'AQ' => 'Antarctica',
        'AR' => 'Argentina',
        'AS' => 'American Samoa',
        'AT' => 'Austria',
        'AU' => 'Australia',
        'AW' => 'Aruba',
        'AX' => 'Åland Islands',
        'AZ' => 'Azerbaijan',
        'BA' => 'Bosnia & Herzegovina',
        'BB' => 'Barbados',
        'BD' => 'Bangladesh',
        'BE' => 'Belgium',
        'BF' => 'Burkina Faso',
        'BG' => 'Bulgaria',
        'BH' => 'Bahrain',
        'BI' => 'Burundi',
        'BJ' => 'Benin',
        'BL' => 'St. Barthélemy',
        'BM' => 'Bermuda',
        'BN' => 'Brunei',
        'BO' => 'Bolivia',
        'BQ' => 'Bonaire, Sint Eustatius & Saba',
        'BR' => 'Brazil',
        'BS' => 'Bahamas',
        'BT' => 'Bhutan',
        'BV' => 'Bouvet Island',
        'BW' => 'Botswana',
        'BY' => 'Belarus',
        'BZ' => 'Belize',
        'CA' => 'Canada',
        'CC' => 'Cocos (Keeling) Islands',
        'CD' => 'Congo - Kinshasa',
        'CF' => 'Central African Republic',
        'CG' => 'Congo - Brazzaville',
        'CH' => 'Switzerland',
        'CI' => "Côte d'Ivoire",
        'CK' => 'Cook Islands',
        'CL' => 'Chile',
        'CM' => 'Cameroon',
        'CN' => 'China',
        'CO' => 'Colombia',
        'CR' => 'Costa Rica',
        'CU' => 'Cuba',
        'CV' => 'Cabo Verde',
        'CW' => 'Curaçao',
        'CX' => 'Christmas Island',
        'CY' => 'Cyprus',
        'CZ' => 'Czechia',
        'DE' => 'Germany',
        'DJ' => 'Djibouti',
        'DK' => 'Denmark',
        'DM' => 'Dominica',
        'DO' => 'Dominican Republic',
        'DZ' => 'Algeria',
        'EC' => 'Ecuador',
        'EE' => 'Estonia',
        'EG' => 'Egypt',
        'EH' => 'Western Sahara',
        'ER' => 'Eritrea',
        'ES' => 'Spain',
        'ET' => 'Ethiopia',
        'FI' => 'Finland',
        'FJ' => 'Fiji',
        'FK' => 'Falkland Islands',
        'FM' => 'Micronesia',
        'FO' => 'Faroe Islands',
        'FR' => 'France',
        'GA' => 'Gabon',
        'GB' => 'United Kingdom',
        'GD' => 'Grenada',
        'GE' => 'Georgia',
        'GF' => 'French Guiana',
        'GG' => 'Guernsey',
        'GH' => 'Ghana',
        'GI' => 'Gibraltar',
        'GL' => 'Greenland',
        'GM' => 'Gambia',
        'GN' => 'Guinea',
        'GP' => 'Guadeloupe',
        'GQ' => 'Equatorial Guinea',
        'GR' => 'Greece',
        'GS' => 'South Georgia & South Sandwich Islands',
        'GT' => 'Guatemala',
        'GU' => 'Guam',
        'GW' => 'Guinea-Bissau',
        'GY' => 'Guyana',
        'HK' => 'Hong Kong',
        'HM' => 'Heard & McDonald Islands',
        'HN' => 'Honduras',
        'HR' => 'Croatia',
        'HT' => 'Haiti',
        'HU' => 'Hungary',
        'ID' => 'Indonesia',
        'IE' => 'Ireland',
        'IL' => 'Israel',
        'IM' => 'Isle of Man',
        'IN' => 'India',
        'IO' => 'British Indian Ocean Territory',
        'IQ' => 'Iraq',
        'IR' => 'Iran',
        'IS' => 'Iceland',
        'IT' => 'Italy',
        'JE' => 'Jersey',
        'JM' => 'Jamaica',
        'JO' => 'Jordan',
        'JP' => 'Japan',
        'KE' => 'Kenya',
        'KG' => 'Kyrgyzstan',
        'KH' => 'Cambodia',
        'KI' => 'Kiribati',
        'KM' => 'Comoros',
        'KN' => 'St. Kitts & Nevis',
        'KP' => 'North Korea',
        'KR' => 'South Korea',
        'KW' => 'Kuwait',
        'KY' => 'Cayman Islands',
        'KZ' => 'Kazakhstan',
        'LA' => 'Laos',
        'LB' => 'Lebanon',
        'LC' => 'St. Lucia',
        'LI' => 'Liechtenstein',
        'LK' => 'Sri Lanka',
        'LR' => 'Liberia',
        'LS' => 'Lesotho',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'LV' => 'Latvia',
        'LY' => 'Libya',
        'MA' => 'Morocco',
        'MC' => 'Monaco',
        'MD' => 'Moldova',
        'ME' => 'Montenegro',
        'MF' => 'St. Martin',
        'MG' => 'Madagascar',
        'MH' => 'Marshall Islands',
        'MK' => 'North Macedonia',
        'ML' => 'Mali',
        'MM' => 'Myanmar',
        'MN' => 'Mongolia',
        'MO' => 'Macao',
        'MP' => 'Northern Mariana Islands',
        'MQ' => 'Martinique',
        'MR' => 'Mauritania',
        'MS' => 'Montserrat',
        'MT' => 'Malta',
        'MU' => 'Mauritius',
        'MV' => 'Maldives',
        'MW' => 'Malawi',
        'MX' => 'Mexico',
        'MY' => 'Malaysia',
        'MZ' => 'Mozambique',
        'NA' => 'Namibia',
        'NC' => 'New Caledonia',
        'NE' => 'Niger',
        'NF' => 'Norfolk Island',
        'NG' => 'Nigeria',
        'NI' => 'Nicaragua',
        'NL' => 'Netherlands',
        'NO' => 'Norway',
        'NP' => 'Nepal',
        'NR' => 'Nauru',
        'NU' => 'Niue',
        'NZ' => 'New Zealand',
        'OM' => 'Oman',
        'PA' => 'Panama',
        'PE' => 'Peru',
        'PF' => 'French Polynesia',
        'PG' => 'Papua New Guinea',
        'PH' => 'Philippines',
        'PK' => 'Pakistan',
        'PL' => 'Poland',
        'PM' => 'St. Pierre & Miquelon',
        'PN' => 'Pitcairn Islands',
        'PR' => 'Puerto Rico',
        'PS' => 'Palestine',
        'PT' => 'Portugal',
        'PW' => 'Palau',
        'PY' => 'Paraguay',
        'QA' => 'Qatar',
        'RE' => 'Réunion',
        'RO' => 'Romania',
        'RS' => 'Serbia',
        'RU' => 'Russia',
        'RW' => 'Rwanda',
        'SA' => 'Saudi Arabia',
        'SB' => 'Solomon Islands',
        'SC' => 'Seychelles',
        'SD' => 'Sudan',
        'SE' => 'Sweden',
        'SG' => 'Singapore',
        'SH' => 'St. Helena',
        'SI' => 'Slovenia',
        'SJ' => 'Svalbard & Jan Mayen',
        'SK' => 'Slovakia',
        'SL' => 'Sierra Leone',
        'SM' => 'San Marino',
        'SN' => 'Senegal',
        'SO' => 'Somalia',
        'SR' => 'Suriname',
        'SS' => 'South Sudan',
        'ST' => 'São Tomé & Príncipe',
        'SV' => 'El Salvador',
        'SX' => 'Sint Maarten',
        'SY' => 'Syria',
        'SZ' => 'Eswatini',
        'TC' => 'Turks & Caicos Islands',
        'TD' => 'Chad',
        'TF' => 'French Southern Territories',
        'TG' => 'Togo',
        'TH' => 'Thailand',
        'TJ' => 'Tajikistan',
        'TK' => 'Tokelau',
        'TL' => 'Timor-Leste',
        'TM' => 'Turkmenistan',
        'TN' => 'Tunisia',
        'TO' => 'Tonga',
        'TR' => 'Türkiye',
        'TT' => 'Trinidad & Tobago',
        'TV' => 'Tuvalu',
        'TW' => 'Taiwan',
        'TZ' => 'Tanzania',
        'UA' => 'Ukraine',
        'UG' => 'Uganda',
        'UM' => 'U.S. Minor Outlying Islands',
        'US' => 'United States',
        'UY' => 'Uruguay',
        'UZ' => 'Uzbekistan',
        'VA' => 'Holy See (Vatican City)',
        'VC' => 'St. Vincent & Grenadines',
        'VE' => 'Venezuela',
        'VG' => 'British Virgin Islands',
        'VI' => 'U.S. Virgin Islands',
        'VN' => 'Vietnam',
        'VU' => 'Vanuatu',
        'WF' => 'Wallis & Futuna',
        'WS' => 'Samoa',
        'YE' => 'Yemen',
        'YT' => 'Mayotte',
        'ZA' => 'South Africa',
        'ZM' => 'Zambia',
        'ZW' => 'Zimbabwe',
    ];
}
