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
        Route::get('accounts/{id}/activate', 'Api\Admin\AccountController@activate');
        Route::get('accounts/{id}/deactivate', 'Api\Admin\AccountController@deactivate');
        Route::get('accounts/{id}/provision', 'Api\Admin\AccountController@provision');

        Route::post('accounts/{id}/recover-by-email', 'Api\Admin\AccountController@recoverByEmail');

        Route::post('accounts', 'Api\Admin\AccountController@store');
        Route::get('accounts', 'Api\Admin\AccountController@index');
        Route::get('accounts/{sip}/search', 'Api\Admin\AccountController@search');
        Route::get('accounts/{email}/search-by-email', 'Api\Admin\AccountController@searchByEmail');
        Route::get('accounts/{id}', 'Api\Admin\AccountController@show');
        Route::delete('accounts/{id}', 'Api\Admin\AccountController@destroy');

        // Account actions
        Route::get('accounts/{id}/actions', 'Api\Admin\AccountActionController@index');
        Route::get('accounts/{id}/actions/{action_id}', 'Api\Admin\AccountActionController@show');
        Route::post('accounts/{id}/actions', 'Api\Admin\AccountActionController@store');
        Route::delete('accounts/{id}/actions/{action_id}', 'Api\Admin\AccountActionController@destroy');
        Route::put('accounts/{id}/actions/{action_id}', 'Api\Admin\AccountActionController@update');

        // Account contacts
        Route::get('accounts/{id}/contacts', 'Api\Admin\AccountContactController@index');
        Route::get('accounts/{id}/contacts/{contact_id}', 'Api\Admin\AccountContactController@show');
        Route::post('accounts/{id}/contacts/{contact_id}', 'Api\Admin\AccountContactController@add');
        Route::delete('accounts/{id}/contacts/{contact_id}', 'Api\Admin\AccountContactController@remove');

        // Account types
        Route::get('account_types', 'Api\Admin\AccountTypeController@index');
        Route::get('account_types/{id}', 'Api\Admin\AccountTypeController@show');
        Route::post('account_types', 'Api\Admin\AccountTypeController@store');
        Route::delete('account_types/{id}', 'Api\Admin\AccountTypeController@destroy');
        Route::put('account_types/{id}', 'Api\Admin\AccountTypeController@update');

        Route::post('accounts/{id}/types/{type_id}', 'Api\Admin\AccountController@typeAdd');
        Route::delete('accounts/{id}/types/{type_id}', 'Api\Admin\AccountController@typeRemove');
    });
});
