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

use App\Http\Controllers\Api\Account\VcardsStorageController;
use App\Http\Controllers\Api\Admin\AccountActionController;
use App\Http\Controllers\Api\Admin\AccountContactController;
use App\Http\Controllers\Api\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Api\Admin\AccountDictionaryController;
use App\Http\Controllers\Api\Admin\AccountTypeController;
use App\Http\Controllers\Api\Admin\ContactsListController;
use App\Http\Controllers\Api\Admin\SipDomainController;
use App\Http\Controllers\Api\Admin\VcardsStorageController as AdminVcardsStorageController;
use App\Http\Controllers\Api\StatisticsMessageController;
use App\Http\Controllers\Api\StatisticsCallController;
use Illuminate\Http\Request;

Route::get('/', 'Api\ApiController@documentation')->name('api');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('ping', 'Api\PingController@ping');

Route::post('account_creation_request_tokens', 'Api\Account\CreationRequestToken@create');
Route::post('account_creation_tokens/send-by-push', 'Api\Account\CreationTokenController@sendByPush');
Route::post('account_creation_tokens/using-account-creation-request-token', 'Api\Account\CreationTokenController@usingAccountRequestToken');
Route::post('accounts/with-account-creation-token', 'Api\Account\AccountController@store');

Route::get('accounts/{sip}/info', 'Api\Account\AccountController@info');

// Deprecated endpoints
Route::post('accounts/{sip}/activate/email', 'Api\Account\AccountController@activateEmail');
Route::post('accounts/{sip}/activate/phone', 'Api\Account\AccountController@activatePhone');

// Deprecated endpoints /!\ Dangerous endpoints
Route::post('accounts/public', 'Api\Account\AccountController@storePublic');
Route::get('accounts/{sip}/recover/{recovery_key}', 'Api\Account\AccountController@recoverUsingKey');
Route::post('accounts/recover-by-phone', 'Api\Account\AccountController@recoverByPhone');
Route::get('accounts/{phone}/info-by-phone', 'Api\Account\AccountController@phoneInfo');

Route::post('accounts/auth_token', 'Api\Account\AuthTokenController@store');

Route::get('accounts/me/api_key/{auth_token}', 'Api\Account\ApiKeyController@generateFromToken')->middleware('cookie', 'cookie.encrypt');

Route::get('phone_countries', 'Api\PhoneCountryController@index');

Route::group(['middleware' => ['auth.jwt', 'auth.digest_or_key', 'auth.check_blocked']], function () {
    Route::get('accounts/auth_token/{auth_token}/attach', 'Api\Account\AuthTokenController@attach');
    Route::post('account_creation_tokens/consume', 'Api\Account\CreationTokenController@consume');

    Route::prefix('accounts/me')->group(function () {
        Route::get('api_key', 'Api\Account\ApiKeyController@generate')->middleware('cookie', 'cookie.encrypt');

        Route::get('services/turn', 'Api\Account\AccountController@turnService');

        Route::get('/', 'Api\Account\AccountController@show');
        Route::delete('/', 'Api\Account\AccountController@delete');
        Route::get('provision', 'Api\Account\AccountController@provision');

        Route::post('phone/request', 'Api\Account\PhoneController@requestUpdate');
        Route::post('phone', 'Api\Account\PhoneController@update');

        Route::get('devices', 'Api\Account\DeviceController@index');
        Route::delete('devices/{uuid}', 'Api\Account\DeviceController@destroy');

        Route::post('email/request', 'Api\Account\EmailController@requestUpdate');
        Route::post('email', 'Api\Account\EmailController@update');

        Route::post('password', 'Api\Account\PasswordController@update');

        Route::get('contacts/{sip}', 'Api\Account\ContactController@show');
        Route::get('contacts', 'Api\Account\ContactController@index');

        Route::apiResource('vcards-storage', VcardsStorageController::class);
    });

    Route::group(['middleware' => ['auth.admin']], function () {
        if (!empty(config('app.linphone_daemon_unix_pipe'))) {
            Route::post('messages', 'Api\Admin\MessageController@send');
        }

        // Super admin
        Route::group(['middleware' => ['auth.super_admin']], function () {
            Route::prefix('sip_domains')->controller(SipDomainController::class)->group(function () {
                Route::get('/', 'index');
                Route::get('{domain}', 'show');
                Route::post('/', 'store');
                Route::put('{domain}', 'update');
                Route::delete('{domain}', 'destroy');
            });
        });

        // Account creation token
        Route::post('account_creation_tokens', 'Api\Admin\AccountCreationTokenController@create');

        // Accounts
        Route::prefix('accounts')->controller(AdminAccountController::class)->group(function () {
            Route::post('{account_id}/activate', 'activate');
            Route::post('{account_id}/deactivate', 'deactivate');
            Route::post('{account_id}/block', 'block');
            Route::post('{account_id}/unblock', 'unblock');
            Route::get('{account_id}/provision', 'provision');

            Route::post('/', 'store');
            Route::put('{account_id}', 'update');
            Route::get('/', 'index');
            Route::get('{account_id}', 'show');
            Route::delete('{account_id}', 'destroy');
            Route::get('{sip}/search', 'search');
            Route::get('{email}/search-by-email', 'searchByEmail');

            Route::get('{account_id}/devices', 'Api\Admin\DeviceController@index');
            Route::delete('{account_id}/devices/{uuid}', 'Api\Admin\DeviceController@destroy');

            Route::post('{account_id}/types/{type_id}', 'typeAdd');
            Route::delete('{account_id}/types/{type_id}', 'typeRemove');

            Route::post('{account_id}/contacts_lists/{contacts_list_id}', 'contactsListAdd');
            Route::delete('{account_id}/contacts_lists/{contacts_list_id}', 'contactsListRemove');
        });

        // Account contacts
        Route::prefix('accounts/{id}/contacts')->controller(AccountContactController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('{contact_id}', 'show');
            Route::post('{contact_id}', 'add');
            Route::delete('{contact_id}', 'remove');
        });

        Route::apiResource('accounts/{id}/actions', AccountActionController::class);
        Route::apiResource('account_types', AccountTypeController::class);
        Route::apiResource('accounts/{account_id}/vcards-storage', AdminVcardsStorageController::class);

        Route::apiResource('contacts_lists', ContactsListController::class);
        Route::prefix('contacts_lists')->controller(ContactsListController::class)->group(function () {
            Route::post('{id}/contacts/{contacts_id}', 'contactAdd');
            Route::delete('{id}/contacts/{contacts_id}', 'contactRemove');
        });

        Route::prefix('accounts/{id}/dictionary')->controller(AccountDictionaryController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('{key}', 'show');
            Route::post('{key}', 'set');
            Route::delete('{key}', 'destroy');
        });

        Route::prefix('statistics/messages')->controller(StatisticsMessageController::class)->group(function () {
            Route::post('/', 'store');
            Route::patch('{message_id}/to/{to}/devices/{device_id}', 'storeDevice');
        });

        Route::prefix('statistics/calls')->controller(StatisticsCallController::class)->group(function () {
            Route::post('/', 'store');
            Route::patch('{call_id}', 'update');
            Route::patch('{call_id}/devices/{device_id}', 'storeDevice');
        });
    });
});
