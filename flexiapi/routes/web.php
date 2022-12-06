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

Route::get('/', 'Account\AccountController@home')->name('account.home');
Route::get('documentation', 'Account\AccountController@documentation')->name('account.documentation');

if (config('app.web_panel')) {
    Route::get('login', 'Account\AuthenticateController@login')->name('account.login');
    Route::post('authenticate', 'Account\AuthenticateController@authenticate')->name('account.authenticate');

    Route::get('login/email', 'Account\AuthenticateController@loginEmail')->name('account.login_email');
    Route::post('authenticate/email', 'Account\AuthenticateController@authenticateEmail')->name('account.authenticate.email');
    Route::get('authenticate/email/check/{sip}', 'Account\AuthenticateController@checkEmail')->name('account.check.email');
    Route::get('authenticate/email/{code}', 'Account\AuthenticateController@validateEmail')->name('account.authenticate.email_confirm');

    Route::get('login/phone', 'Account\AuthenticateController@loginPhone')->name('account.login_phone');
    Route::post('authenticate/phone', 'Account\AuthenticateController@authenticatePhone')->name('account.authenticate.phone');
    Route::post('authenticate/phone/confirm', 'Account\AuthenticateController@validatePhone')->name('account.authenticate.phone_confirm');

    Route::get('authenticate/qrcode/{token?}', 'Account\AuthenticateController@loginAuthToken')->name('account.authenticate.auth_token');
}

Route::group(['middleware' => 'auth.digest_or_key'], function () {
    Route::get('provisioning/me', 'Account\ProvisioningController@me')->name('provisioning.me');

    // Vcard 4.0
    Route::get('contacts/vcard/{sip}', 'Account\ContactVcardController@show')->name('account.contacts.vcard.show');
    Route::get('contacts/vcard', 'Account\ContactVcardController@index')->name('account.contacts.vcard.index');
});

Route::get('provisioning/auth_token/{auth_token}', 'Account\ProvisioningController@authToken')->name('provisioning.auth_token');
Route::get('provisioning/qrcode/{provisioning_token}', 'Account\ProvisioningController@qrcode')->name('provisioning.qrcode');
Route::get('provisioning/{provisioning_token?}', 'Account\ProvisioningController@show')->name('provisioning.show');

if (publicRegistrationEnabled()) {
    if (config('app.phone_authentication')) {
        Route::get('register/phone', 'Account\RegisterController@registerPhone')->name('account.register.phone');
        Route::post('register/phone', 'Account\RegisterController@storePhone')->name('account.store.phone');
    }

    Route::get('register', 'Account\RegisterController@register')->name('account.register');
    Route::get('register/email', 'Account\RegisterController@registerEmail')->name('account.register.email');
    Route::post('register/email', 'Account\RegisterController@storeEmail')->name('account.store.email');
}

if (config('app.web_panel')) {
    Route::group(['middleware' => 'auth'], function () {
        Route::get('panel', 'Account\AccountController@panel')->name('account.panel');
        Route::get('logout', 'Account\AuthenticateController@logout')->name('account.logout');

        Route::post('api_key', 'Account\AccountController@generateApiKey')->name('account.api_key.generate');

        Route::get('delete', 'Account\AccountController@delete')->name('account.delete');
        Route::delete('delete', 'Account\AccountController@destroy')->name('account.destroy');

        Route::get('email', 'Account\EmailController@show')->name('account.email');
        Route::post('email/request', 'Account\EmailController@requestUpdate')->name('account.email.request_update');
        Route::get('email/{hash}', 'Account\EmailController@update')->name('account.email.update');
        Route::get('password', 'Account\PasswordController@show')->name('account.password');
        Route::post('password', 'Account\PasswordController@update')->name('account.password.update');

        Route::get('devices', 'Account\DeviceController@index')->name('account.device.index');
        Route::get('devices/delete/{id}', 'Account\DeviceController@delete')->name('account.device.delete');
        Route::delete('devices', 'Account\DeviceController@destroy')->name('account.device.destroy');

        Route::post('auth_tokens', 'Account\AuthTokenController@create')->name('account.auth_tokens.create');

        Route::get('auth_tokens/auth/external/{token}', 'Account\AuthTokenController@authExternal')->name('auth_tokens.auth.external');
    });

    Route::get('auth_tokens/qrcode/{token}', 'Account\AuthTokenController@qrcode')->name('auth_tokens.qrcode');
    Route::get('auth_tokens/auth/{token}', 'Account\AuthTokenController@auth')->name('auth_tokens.auth');

    Route::group(['middleware' => 'auth.admin'], function () {
        // Statistics
        Route::get('admin/statistics/day', 'Admin\StatisticsController@showDay')->name('admin.statistics.show.day');
        Route::get('admin/statistics/week', 'Admin\StatisticsController@showWeek')->name('admin.statistics.show.week');
        Route::get('admin/statistics/month', 'Admin\StatisticsController@showMonth')->name('admin.statistics.show.month');

        // Account types
        Route::get('admin/accounts/types', 'Admin\AccountTypeController@index')->name('admin.account.type.index');
        Route::get('admin/accounts/types/create', 'Admin\AccountTypeController@create')->name('admin.account.type.create');
        Route::post('admin/accounts/types', 'Admin\AccountTypeController@store')->name('admin.account.type.store');
        Route::get('admin/accounts/types/{type_id}/edit', 'Admin\AccountTypeController@edit')->name('admin.account.type.edit');
        Route::put('admin/accounts/types/{type_id}', 'Admin\AccountTypeController@update')->name('admin.account.type.update');
        Route::get('admin/accounts/types/{type_id}/delete', 'Admin\AccountTypeController@delete')->name('admin.account.type.delete');
        Route::delete('admin/accounts/types/{type_id}', 'Admin\AccountTypeController@destroy')->name('admin.account.type.destroy');

        Route::get('admin/accounts/{account}/types/create', 'Admin\AccountAccountTypeController@create')->name('admin.account.account_type.create');
        Route::post('admin/accounts/{account}/types', 'Admin\AccountAccountTypeController@store')->name('admin.account.account_type.store');
        Route::delete('admin/accounts/{account}/types/{type_id}', 'Admin\AccountAccountTypeController@destroy')->name('admin.account.account_type.destroy');

        // Contacts
        Route::get('admin/accounts/{account}/contacts/create', 'Admin\AccountContactController@create')->name('admin.account.contact.create');
        Route::post('admin/accounts/{account}/contacts', 'Admin\AccountContactController@store')->name('admin.account.contact.store');
        Route::get('admin/accounts/{account}/contacts/{contact_id}/delete', 'Admin\AccountContactController@delete')->name('admin.account.contact.delete');
        Route::delete('admin/accounts/{account}/contacts', 'Admin\AccountContactController@destroy')->name('admin.account.contact.destroy');

        // Accounts
        Route::get('admin/accounts/{account}/show', 'Admin\AccountController@show')->name('admin.account.show');

        Route::get('admin/accounts/{account}/activate', 'Admin\AccountController@activate')->name('admin.account.activate');
        Route::get('admin/accounts/{account}/deactivate', 'Admin\AccountController@deactivate')->name('admin.account.deactivate');

        Route::get('admin/accounts/{account}/external_account/attach', 'Admin\AccountController@attachExternalAccount')->name('admin.account.external_account.attach');

        Route::get('admin/accounts/{account}/admin', 'Admin\AccountController@admin')->name('admin.account.admin');
        Route::get('admin/accounts/{id}/unadmin', 'Admin\AccountController@unadmin')->name('admin.account.unadmin');

        Route::get('admin/accounts/{account}/provision', 'Admin\AccountController@provision')->name('admin.account.provision');

        Route::get('admin/accounts/create', 'Admin\AccountController@create')->name('admin.account.create');
        Route::post('admin/accounts', 'Admin\AccountController@store')->name('admin.account.store');

        Route::get('admin/accounts/{account}/edit', 'Admin\AccountController@edit')->name('admin.account.edit');
        Route::put('admin/accounts/{id}', 'Admin\AccountController@update')->name('admin.account.update');

        Route::get('admin/accounts/{account}/delete', 'Admin\AccountController@delete')->name('admin.account.delete');
        Route::delete('admin/accounts', 'Admin\AccountController@destroy')->name('admin.account.destroy');

        Route::get('admin/accounts/{search?}', 'Admin\AccountController@index')->name('admin.account.index');
        Route::post('admin/accounts/search', 'Admin\AccountController@search')->name('admin.account.search');

        // Account actions
        Route::get('admin/accounts/{account}/actions/create', 'Admin\AccountActionController@create')->name('admin.account.action.create');
        Route::post('admin/accounts/{account}/actions', 'Admin\AccountActionController@store')->name('admin.account.action.store');
        Route::get('admin/accounts/{account}/actions/{action_id}/edit', 'Admin\AccountActionController@edit')->name('admin.account.action.edit');
        Route::put('admin/accounts/{account}/actions/{action_id}', 'Admin\AccountActionController@update')->name('admin.account.action.update');
        Route::get('admin/accounts/{account}/actions/{action_id}/delete', 'Admin\AccountActionController@delete')->name('admin.account.action.delete');
        Route::delete('admin/accounts/{account}/actions/{action_id}', 'Admin\AccountActionController@destroy')->name('admin.account.action.destroy');
    });
}
