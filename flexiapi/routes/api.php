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
use App\Http\Controllers\Api\Account\VoicemailController;
use App\Http\Controllers\Api\Admin\Account\ActionController;
use App\Http\Controllers\Api\Admin\Account\CardDavCredentialsController;
use App\Http\Controllers\Api\Admin\Account\ContactController;
use App\Http\Controllers\Api\Admin\Account\DictionaryController;
use App\Http\Controllers\Api\Admin\Account\TypeController;
use App\Http\Controllers\Api\Admin\Account\VoicemailController as AdminVoicemailController;
use App\Http\Controllers\Api\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Api\Admin\ContactsListController;
use App\Http\Controllers\Api\Admin\ExternalAccountController;
use App\Http\Controllers\Api\Admin\Space\CardDavServerController;
use App\Http\Controllers\Api\Admin\Space\EmailServerController;
use App\Http\Controllers\Api\Admin\SpaceController;
use App\Http\Controllers\Api\Admin\VcardsStorageController as AdminVcardsStorageController;
use App\Http\Controllers\Api\StatisticsCallController;
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
Route::post('account_recovery_tokens/send-by-push', 'Api\Account\RecoveryTokenController@sendByPush');

Route::get('accounts/{sip}/info', 'Api\Account\AccountController@info');

Route::post('accounts/auth_token', 'Api\Account\AuthTokenController@store');

Route::get('accounts/me/api_key/{auth_token}', 'Api\Account\ApiKeyController@generateFromToken')->middleware('cookie', 'cookie.encrypt');

Route::get('phone_countries', 'Api\PhoneCountryController@index');
Route::get('files/{uuid}/{name}', 'Api\Account\FileController@show')->name('file.show');

Route::group(['middleware' => ['auth.jwt', 'auth.digest_or_key', 'auth.check_blocked']], function () {
    Route::get('accounts/auth_token/{auth_token}/attach', 'Api\Account\AuthTokenController@attach');
    Route::post('account_creation_tokens/consume', 'Api\Account\CreationTokenController@consume');
    Route::post('files/{uuid}', 'Api\Account\FileController@upload')->name('file.upload');

    Route::post('push_notification', 'Api\Account\PushNotificationController@push');

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
        Route::apiResource('voicemails', VoicemailController::class, ['only' => ['index', 'show', 'store', 'destroy']]);
    });

    Route::group(['middleware' => ['auth.admin']], function () {
        if (!empty(config('app.linphone_daemon_unix_pipe'))) {
            Route::post('messages', 'Api\Admin\MessageController@send');
        }

        // Super admin
        Route::group(['middleware' => ['auth.super_admin']], function () {
            Route::apiResource('spaces', SpaceController::class);

            Route::prefix('spaces/{domain}/email')->controller(EmailServerController::class)->group(function () {
                Route::get('/', 'show');
                Route::post('/', 'store');
                Route::delete('/', 'destroy');
            });

            Route::apiResource('spaces/{domain}/carddavs', CardDavServerController::class);
        });

        // Account creation token
        Route::post('account_creation_tokens', 'Api\Admin\Account\CreationTokenController@create');

        // Accounts
        Route::prefix('accounts')->controller(AdminAccountController::class)->group(function () {
            Route::post('{account_id}/activate', 'activate');
            Route::post('{account_id}/deactivate', 'deactivate');
            Route::post('{account_id}/block', 'block');
            Route::post('{account_id}/unblock', 'unblock');
            Route::get('{account_id}/provision', 'provision');
            Route::post('{account_id}/send_provisioning_email', 'sendProvisioningEmail');
            Route::post('{account_id}/send_reset_password_email', 'sendResetPasswordEmail');

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
        Route::prefix('accounts/{id}/contacts')->controller(ContactController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('{contact_id}', 'show');
            Route::post('{contact_id}', 'add');
            Route::delete('{contact_id}', 'remove');
        });

        Route::group(['middleware' => ['feature.carddav_user_credentials']], function () {
            Route::apiResource('accounts/{id}/carddavs', CardDavCredentialsController::class, ['only' => ['index', 'show', 'update', 'destroy']]);
        });

        Route::apiResource('accounts/{id}/actions', ActionController::class);
        Route::apiResource('account_types', TypeController::class);
        Route::apiResource('accounts/{account_id}/vcards-storage', AdminVcardsStorageController::class);
        Route::apiResource('accounts/{id}/voicemails', AdminVoicemailController::class, ['only' => ['index', 'show', 'store', 'destroy']]);

        Route::apiResource('contacts_lists', ContactsListController::class);
        Route::prefix('contacts_lists')->controller(ContactsListController::class)->group(function () {
            Route::post('{id}/contacts/{contacts_id}', 'contactAdd');
            Route::delete('{id}/contacts/{contacts_id}', 'contactRemove');
        });

        Route::prefix('accounts/{id}/dictionary')->controller(DictionaryController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('{key}', 'show');
            Route::post('{key}', 'set');
            Route::delete('{key}', 'destroy');
        });

        Route::prefix('accounts/{id}/external')->controller(ExternalAccountController::class)->group(function () {
            Route::get('/', 'show');
            Route::post('/', 'store');
            Route::delete('/', 'destroy');
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
