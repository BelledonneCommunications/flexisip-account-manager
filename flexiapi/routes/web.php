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

//Route::get('/', 'HomeController@index')->name('home');

Route::get('/', 'Account\AccountController@home')->name('account.home');
Route::get('terms', 'Account\AccountController@terms')->name('account.terms');
Route::get('privacy', 'Account\AccountController@privacy')->name('account.privacy');

Route::get('login', 'Account\AuthenticateController@login')->name('account.login');
Route::post('authenticate', 'Account\AuthenticateController@authenticate')->name('account.authenticate');

Route::get('login/email', 'Account\AuthenticateController@loginEmail')->name('account.login_email');
Route::post('authenticate/email', 'Account\AuthenticateController@authenticateEmail')->name('account.authenticate.email');
Route::get('authenticate/email/{code}', 'Account\AuthenticateController@validateEmail')->name('account.authenticate.email_confirm');

Route::get('login/phone', 'Account\AuthenticateController@loginPhone')->name('account.login_phone');
Route::post('authenticate/phone', 'Account\AuthenticateController@authenticatePhone')->name('account.authenticate.phone');
Route::post('authenticate/phone/confirm', 'Account\AuthenticateController@validatePhone')->name('account.authenticate.phone_confirm');

Route::get('register', 'Account\RegisterController@register')->name('account.register');

if (config('app.phone_authentication')) {
    Route::get('register/phone', 'Account\RegisterController@registerPhone')->name('account.register.phone');
    Route::post('register/phone', 'Account\RegisterController@storePhone')->name('account.store.phone');
}

Route::get('register/email', 'Account\RegisterController@registerEmail')->name('account.register.email');
Route::post('register/email', 'Account\RegisterController@storeEmail')->name('account.store.email');

Route::group(['middleware' => 'auth'], function () {
    Route::get('panel', 'Account\AccountController@panel')->name('account.panel');
    Route::get('logout', 'Account\AuthenticateController@logout')->name('account.logout');

    Route::get('delete', 'Account\AccountController@delete')->name('account.delete');
    Route::delete('delete', 'Account\AccountController@destroy')->name('account.destroy');

    Route::get('email', 'Account\EmailController@show')->name('account.email');
    Route::post('email/request', 'Account\EmailController@requestUpdate')->name('account.email.request_update');
    Route::get('email/{hash}', 'Account\EmailController@update')->name('account.email.update');
    Route::get('password', 'Account\PasswordController@show')->name('account.password');
    Route::post('password', 'Account\PasswordController@update')->name('account.password.update');

    Route::get('devices', 'Account\DeviceController@index')->name('account.device.index');
    Route::get('devices/delete/{id}', 'Account\DeviceController@delete')->name('account.device.delete');
    Route::delete('devices/{id}', 'Account\DeviceController@destroy')->name('account.device.destroy');
});

Route::group(['middleware' => 'auth.admin'], function () {
    Route::get('admin/accounts/{search?}', 'Admin\AccountController@index')->name('admin.account.index');
    Route::post('admin/search', 'Admin\AccountController@search')->name('admin.account.search');

    Route::post('admin/api_key', 'Admin\AccountController@generateApiKey')->name('admin.api_key.generate');

    Route::get('admin/accounts/show/{id}', 'Admin\AccountController@show')->name('admin.account.show');
    Route::get('admin/accounts/{id}/activate', 'Admin\AccountController@activate')->name('admin.account.activate');
    Route::get('admin/accounts/{id}/deactivate', 'Admin\AccountController@deactivate')->name('admin.account.deactivate');
    Route::get('admin/accounts/{id}/admin', 'Admin\AccountController@admin')->name('admin.account.admin');
    Route::get('admin/accounts/{id}/unadmin', 'Admin\AccountController@unadmin')->name('admin.account.unadmin');
});