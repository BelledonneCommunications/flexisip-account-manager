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

use App\Http\Controllers\Api\Account\AccountController;
use App\Http\Controllers\Api\Account\ApiKeyController;
use App\Http\Controllers\Api\Account\AuthTokenController;
use App\Http\Controllers\Api\Account\ContactController;
use App\Http\Controllers\Api\Account\CreationRequestToken;
use App\Http\Controllers\Api\Account\CreationTokenController;
use App\Http\Controllers\Api\Account\DeviceController;
use App\Http\Controllers\Api\Account\EmailController;
use App\Http\Controllers\Api\Account\FileController;
use App\Http\Controllers\Api\Account\PasswordController;
use App\Http\Controllers\Api\Account\PhoneController;
use App\Http\Controllers\Api\Account\PushNotificationController;
use App\Http\Controllers\Api\Account\RecoveryTokenController;
use App\Http\Controllers\Api\Account\VcardsStorageController;
use App\Http\Controllers\Api\Account\VoicemailController;
use App\Http\Controllers\Api\Admin\Account\ActionController;
use App\Http\Controllers\Api\Admin\Account\CardDavCredentialsController;
use App\Http\Controllers\Api\Admin\Account\ContactController as AdminContactController;
use App\Http\Controllers\Api\Admin\Account\CreationTokenController as AdminCreationTokenController;
use App\Http\Controllers\Api\Admin\Account\DictionaryController;
use App\Http\Controllers\Api\Admin\Account\TypeController;
use App\Http\Controllers\Api\Admin\Account\VoicemailController as AdminVoicemailController;
use App\Http\Controllers\Api\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Api\Admin\ExternalAccountController;
use App\Http\Controllers\Api\Admin\MessageController;
use App\Http\Controllers\Api\Admin\PhoneCountryController as AdminPhoneCountryController;
use App\Http\Controllers\Api\Admin\Space\CardDavServerController;
use App\Http\Controllers\Api\Admin\Space\ContactsListController;
use App\Http\Controllers\Api\Admin\Space\EmailServerController;
use App\Http\Controllers\Api\Admin\SpaceController;
use App\Http\Controllers\Api\Admin\VcardsStorageController as AdminVcardsStorageController;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\PhoneCountryController;
use App\Http\Controllers\Api\PingController;
use App\Http\Controllers\Api\StatisticsCallController;
use App\Http\Controllers\Api\StatisticsMessageController;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Http\Request;

Route::get('/', [ApiController::class, 'documentation'])->name('api');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('ping', [PingController::class, 'ping']);

Route::post('account_creation_request_tokens', [CreationRequestToken::class, 'create']);
Route::post('account_creation_tokens/send-by-push', [CreationTokenController::class, 'sendByPush']);
Route::post('account_creation_tokens/using-account-creation-request-token', [CreationTokenController::class, 'usingAccountRequestToken']);
Route::post('accounts/with-account-creation-token', [AccountController::class, 'store']);
Route::post('account_recovery_tokens/send-by-push', [RecoveryTokenController::class, 'sendByPush']);

Route::get('accounts/{sip}/info', [AccountController::class, 'info']);

Route::post('accounts/auth_token', [AuthTokenController::class, 'store']);

Route::get('accounts/me/api_key/{auth_token}', [ApiKeyController::class, 'generateFromToken'])->middleware(AddQueuedCookiesToResponse::class);

Route::get('phone_countries', [PhoneCountryController::class, 'index']);

Route::group(['middleware' => ['auth.jwt', 'auth.digest_or_key', 'auth.check_blocked']], function () {
    Route::post('files/{uuid}', [FileController::class, 'upload'])->name('file.upload');

    Route::get('accounts/auth_token/{auth_token}/attach', [AuthTokenController::class, 'attach']);
    Route::post('account_creation_tokens/consume', [CreationTokenController::class, 'consume']);

    Route::post('push_notification', [PushNotificationController::class, 'push']);

    Route::prefix('accounts/me')->group(function () {
        Route::get('api_key', [ApiKeyController::class, 'generate'])->middleware(AddQueuedCookiesToResponse::class);

        Route::get('services/turn', [AccountController::class, 'turnService']);

        Route::get('/', [AccountController::class, 'show']);
        Route::delete('/', [AccountController::class, 'delete']);
        Route::get('provision', [AccountController::class, 'provision']);

        Route::post('phone/request', [PhoneController::class, 'requestUpdate']);
        Route::post('phone', [PhoneController::class, 'update']);

        Route::get('devices', [DeviceController::class, 'index']);
        Route::delete('devices/{uuid}', [DeviceController::class, 'destroy']);

        Route::post('email/request', [EmailController::class, 'requestUpdate']);
        Route::post('email', [EmailController::class, 'update']);

        Route::post('password', [PasswordController::class, 'update']);

        Route::get('contacts/{sip}', [ContactController::class, 'show']);
        Route::get('contacts', [ContactController::class, 'index']);

        Route::apiResource('vcards-storage', VcardsStorageController::class);
        Route::apiResource('voicemails', VoicemailController::class, ['only' => ['index', 'show', 'store', 'destroy']]);
    });

    Route::group(['middleware' => ['auth.admin']], function () {
        if (!empty(config('app.linphone_daemon_unix_pipe'))) {
            Route::post('messages', [MessageController::class, 'send']);
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

            Route::post('phone_countries/{code}/activate', [AdminPhoneCountryController::class, 'activate']);
            Route::post('phone_countries/{code}/deactivate', [AdminPhoneCountryController::class, 'deactivate']);
        });

        // Account creation token
        Route::post('account_creation_tokens', [AdminCreationTokenController::class, 'create']);

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
            Route::get('/', 'index')->name('accounts.index');
            Route::get('{account_id}', 'show');
            Route::delete('{account_id}', 'destroy');
            Route::get('{sip}/search', 'search');
            Route::get('{email}/search-by-email', 'searchByEmail');

            Route::get('{account_id}/devices', [DeviceController::class, 'index']);
            Route::delete('{account_id}/devices/{uuid}', [DeviceController::class, 'destroy']);

            Route::post('{account_id}/types/{type_id}', 'typeAdd');
            Route::delete('{account_id}/types/{type_id}', 'typeRemove');

            Route::post('{account_id}/contacts_lists/{contacts_list_id}', 'contactsListAdd');
            Route::delete('{account_id}/contacts_lists/{contacts_list_id}', 'contactsListRemove');
        });

        // Account contacts
        Route::prefix('accounts/{id}/contacts')->controller(AdminContactController::class)->group(function () {
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
        Route::apiResource('accounts/{id}/vcards-storage', AdminVcardsStorageController::class);
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
