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

use App\Http\Controllers\Api\Admin\AccountActionController;
use App\Http\Controllers\Api\Admin\AccountContactController;
use App\Http\Controllers\Api\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Api\Admin\AccountTypeController;
use App\Http\Controllers\Api\Admin\ContactsListController;
use App\Http\Controllers\Api\StatisticsMessageController;
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

Route::post('accounts/{sip}/activate/email', 'Api\Account\AccountController@activateEmail');
Route::post('accounts/{sip}/activate/phone', 'Api\Account\AccountController@activatePhone');

// /!\ Dangerous endpoints
Route::post('accounts/public', 'Api\Account\AccountController@storePublic');
Route::get('accounts/{sip}/recover/{recovery_key}', 'Api\Account\AccountController@recoverUsingKey');
Route::post('accounts/recover-by-phone', 'Api\Account\AccountController@recoverByPhone');
Route::get('accounts/{phone}/info-by-phone', 'Api\Account\AccountController@phoneInfo');

Route::post('accounts/auth_token', 'Api\Account\AuthTokenController@store');

Route::get('accounts/me/api_key/{auth_token}', 'Api\Account\ApiKeyController@generateFromToken')->middleware('cookie', 'cookie.encrypt');

Route::group(['middleware' => ['auth.digest_or_key']], function () {
    Route::get('statistic/month', 'Api\StatisticController@month');
    Route::get('statistic/week', 'Api\StatisticController@week');
    Route::get('statistic/day', 'Api\StatisticController@day');

    Route::get('accounts/auth_token/{auth_token}/attach', 'Api\Account\AuthTokenController@attach');

    Route::get('accounts/me/api_key', 'Api\Account\ApiKeyController@generate')->middleware('cookie', 'cookie.encrypt');

    Route::get('accounts/me', 'Api\Account\AccountController@show');
    Route::delete('accounts/me', 'Api\Account\AccountController@delete');
    Route::get('accounts/me/provision', 'Api\Account\AccountController@provision');

    Route::post('accounts/me/phone/request', 'Api\Account\PhoneController@requestUpdate');
    Route::post('accounts/me/phone', 'Api\Account\PhoneController@update');

    Route::get('accounts/me/devices', 'Api\Account\DeviceController@index');
    Route::delete('accounts/me/devices/{uuid}', 'Api\Account\DeviceController@destroy');

    Route::post('accounts/me/email/request', 'Api\Account\EmailController@requestUpdate');
    Route::post('accounts/me/password', 'Api\Account\PasswordController@update');

    Route::get('accounts/me/contacts/{sip}', 'Api\Account\ContactController@show');
    Route::get('accounts/me/contacts', 'Api\Account\ContactController@index');

    Route::group(['middleware' => ['auth.admin']], function () {
        if (!empty(config('app.linphone_daemon_unix_pipe'))) {
            Route::post('messages', 'Api\Admin\MessageController@send');
        }

        // Account creation token
        Route::post('account_creation_tokens', 'Api\Admin\AccountCreationTokenController@create');

        // Accounts
        Route::prefix('accounts')->controller(AdminAccountController::class)->group(function () {
            Route::get('{id}/activate', 'activate');
            Route::get('{id}/deactivate', 'deactivate');
            Route::get('{id}/provision', 'provision');

            Route::post('{id}/recover-by-email', 'recoverByEmail');

            Route::post('/', 'store');
            Route::put('{id}', 'update');
            Route::get('/', 'index');
            Route::get('{id}', 'show');
            Route::delete('{id}', 'destroy');
            Route::get('{sip}/search', 'search');
            Route::get('{email}/search-by-email', 'searchByEmail');

            Route::post('{id}/types/{type_id}', 'typeAdd');
            Route::delete('{id}/types/{type_id}', 'typeRemove');

            Route::post('{id}/contacts_lists/{contacts_list_id}', 'contactsListAdd');
            Route::delete('{id}/contacts_lists/{contacts_list_id}', 'contactsListRemove');
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

        Route::apiResource('contacts_lists', ContactsListController::class);
        Route::prefix('contacts_lists')->controller(ContactsListController::class)->group(function () {
            Route::post('{id}/contacts/{contacts_id}', 'contactAdd');
            Route::delete('{id}/contacts/{contacts_id}', 'contactRemove');
        });

        Route::prefix('statistics/messages')->controller(StatisticsMessageController::class)->group(function () {
            Route::post('/', 'store');
            Route::patch('{message_id}/to/{to}/devices/{device_id}', 'storeDevice');
        });
    });
});
