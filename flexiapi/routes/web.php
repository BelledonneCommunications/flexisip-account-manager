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

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\CreationRequestTokenController;
use App\Http\Controllers\Account\DeviceController;
use App\Http\Controllers\Account\EmailController;
use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Account\PhoneController;
use App\Http\Controllers\Account\ProvisioningController;
use App\Http\Controllers\Account\RecoveryController;

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('account.home');
Route::get('documentation', 'Account\AccountController@documentation')->name('account.documentation');

if (config('app.web_panel')) {
    Route::get('login', 'Account\AuthenticateController@login')->name('account.login');
    Route::post('authenticate', 'Account\AuthenticateController@authenticate')->name('account.authenticate');
    Route::get('authenticate/qrcode/{token?}', 'Account\AuthenticateController@loginAuthToken')->name('account.authenticate.auth_token');
}

Route::prefix('creation_token')->controller(CreationRequestTokenController::class)->group(function () {
    Route::get('check/{token}', 'check')->name('account.creation_request_token.check');
    Route::post('validate', 'validateToken')->name('account.creation_request_token.validate');
});

Route::group(['middleware' => 'auth.digest_or_key'], function () {
    Route::get('provisioning/me', 'Account\ProvisioningController@me')->name('provisioning.me');

    // Vcard 4.0
    Route::get('contacts/vcard/{sip}', 'Account\ContactVcardController@show')->name('account.contacts.vcard.show');
    Route::get('contacts/vcard', 'Account\ContactVcardController@index')->name('account.contacts.vcard.index');
});

Route::prefix('provisioning')->controller(ProvisioningController::class)->group(function () {
    Route::get('auth_token/{auth_token}', 'authToken')->name('provisioning.auth_token');
    Route::get('qrcode/{provisioning_token}', 'qrcode')->name('provisioning.qrcode');
    Route::get('{provisioning_token?}', 'show')->name('provisioning.show');
});

if (publicRegistrationEnabled()) {
    Route::redirect('register', 'register/email')->name('account.register');

    if (config('app.phone_authentication')) {
        Route::get('register/phone', 'Account\RegisterController@registerPhone')->name('account.register.phone');
    }

    Route::get('register/email', 'Account\RegisterController@registerEmail')->name('account.register.email');
    Route::post('accounts', 'Account\AccountController@store')->name('account.store');
}

if (config('app.web_panel')) {
    Route::prefix('recover')->controller(RecoveryController::class)->group(function () {
        Route::get('phone', 'showPhone')->name('account.recovery.show.phone');
        Route::get('email', 'showEmail')->name('account.recovery.show.email');
        Route::post('/', 'send')->name('account.recovery.send');
        Route::post('confirm', 'confirm')->name('account.recovery.confirm');
    });

    Route::middleware(['auth'])->group(function () {
        // Email change and validation
        Route::prefix('email')->controller(EmailController::class)->group(function () {
            Route::get('change', 'change')->name('account.email.change');
            Route::post('change', 'requestChange')->name('account.email.request_change');
            Route::get('validate', 'validateChange')->name('account.email.validate');
            Route::post('/', 'store')->name('account.email.update');
        });

        // Phone change and validation
        Route::prefix('phone')->controller(PhoneController::class)->group(function () {
            Route::get('change', 'change')->name('account.phone.change');
            Route::post('change', 'requestChange')->name('account.phone.request_change');
            Route::get('validate', 'validateChange')->name('account.phone.validate');
            Route::post('/', 'store')->name('account.phone.update');
        });

        Route::controller(AccountController::class)->group(function () {
            Route::get('dashboard', 'panel')->name('account.dashboard');

            Route::post('api_key', 'generateApiKey')->name('account.api_key.generate');

            Route::get('delete', 'delete')->name('account.delete');
            Route::delete('delete', 'destroy')->name('account.destroy');
        });

        Route::get('logout', 'Account\AuthenticateController@logout')->name('account.logout');

        Route::prefix('password')->controller(PasswordController::class)->group(function () {
            Route::get('/', 'show')->name('account.password');
            Route::post('/', 'update')->name('account.password.update');
        });

        Route::prefix('devices')->controller(DeviceController::class)->group(function () {
            Route::get('/', 'index')->name('account.device.index');
            Route::get('delete/{id}', 'delete')->name('account.device.delete');
            Route::delete('/', 'destroy')->name('account.device.destroy');
        });

        Route::post('auth_tokens', 'Account\AuthTokenController@create')->name('account.auth_tokens.create');

        Route::get('auth_tokens/auth/external/{token}', 'Account\AuthTokenController@authExternal')->name('auth_tokens.auth.external');
    });

    Route::get('auth_tokens/qrcode/{token}', 'Account\AuthTokenController@qrcode')->name('auth_tokens.qrcode');
    Route::get('auth_tokens/auth/{token}', 'Account\AuthTokenController@auth')->name('auth_tokens.auth');

    Route::name('admin.')->prefix('admin')->middleware(['auth.admin'])->group(function () {
        // Statistics
        Route::get('statistics/day', 'Admin\StatisticsController@showDay')->name('statistics.show.day');
        Route::get('statistics/week', 'Admin\StatisticsController@showWeek')->name('statistics.show.week');
        Route::get('statistics/month', 'Admin\StatisticsController@showMonth')->name('statistics.show.month');

        Route::prefix('accounts')->group(function () {
            // Account types
            Route::get('types', 'Admin\AccountTypeController@index')->name('account.type.index');
            Route::get('types/create', 'Admin\AccountTypeController@create')->name('account.type.create');
            Route::post('types', 'Admin\AccountTypeController@store')->name('account.type.store');
            Route::get('types/{type_id}/edit', 'Admin\AccountTypeController@edit')->name('account.type.edit');
            Route::put('types/{type_id}', 'Admin\AccountTypeController@update')->name('account.type.update');
            Route::get('types/{type_id}/delete', 'Admin\AccountTypeController@delete')->name('account.type.delete');
            Route::delete('types/{type_id}', 'Admin\AccountTypeController@destroy')->name('account.type.destroy');

            Route::get('{account}/types/create', 'Admin\AccountAccountTypeController@create')->name('account.account_type.create');
            Route::post('{account}/types', 'Admin\AccountAccountTypeController@store')->name('account.account_type.store');
            Route::delete('{account}/types/{type_id}', 'Admin\AccountAccountTypeController@destroy')->name('account.account_type.destroy');

            // Contacts
            Route::get('{account}/contacts/create', 'Admin\AccountContactController@create')->name('account.contact.create');
            Route::post('{account}/contacts', 'Admin\AccountContactController@store')->name('account.contact.store');
            Route::get('{account}/contacts/{contact_id}/delete', 'Admin\AccountContactController@delete')->name('account.contact.delete');
            Route::delete('{account}/contacts', 'Admin\AccountContactController@destroy')->name('account.contact.destroy');

            // Accounts
            Route::get('{account}/show', 'Admin\AccountController@show')->name('account.show');

            Route::get('{account}/activate', 'Admin\AccountController@activate')->name('account.activate');
            Route::get('{account}/deactivate', 'Admin\AccountController@deactivate')->name('account.deactivate');

            Route::get('{account}/external_account/attach', 'Admin\AccountController@attachExternalAccount')->name('account.external_account.attach');

            Route::get('{account}/admin', 'Admin\AccountController@admin')->name('account.admin');
            Route::get('{id}/unadmin', 'Admin\AccountController@unadmin')->name('account.unadmin');

            Route::get('{account}/provision', 'Admin\AccountController@provision')->name('account.provision');

            Route::get('create', 'Admin\AccountController@create')->name('account.create');
            Route::post('accounts', 'Admin\AccountController@store')->name('account.store');

            Route::get('{account}/edit', 'Admin\AccountController@edit')->name('account.edit');
            Route::put('{id}', 'Admin\AccountController@update')->name('account.update');

            Route::get('{account}/delete', 'Admin\AccountController@delete')->name('account.delete');
            Route::delete('accounts', 'Admin\AccountController@destroy')->name('account.destroy');

            Route::get('{search?}', 'Admin\AccountController@index')->name('account.index');
            Route::post('search', 'Admin\AccountController@search')->name('account.search');

            // Account actions
            Route::get('{account}/actions/create', 'Admin\AccountActionController@create')->name('account.action.create');
            Route::post('{account}/actions', 'Admin\AccountActionController@store')->name('account.action.store');
            Route::get('{account}/actions/{action_id}/edit', 'Admin\AccountActionController@edit')->name('account.action.edit');
            Route::put('{account}/actions/{action_id}', 'Admin\AccountActionController@update')->name('account.action.update');
            Route::get('{account}/actions/{action_id}/delete', 'Admin\AccountActionController@delete')->name('account.action.delete');
            Route::delete('{account}/actions/{action_id}', 'Admin\AccountActionController@destroy')->name('account.action.destroy');
        });
    });
}
