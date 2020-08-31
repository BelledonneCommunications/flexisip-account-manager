<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2019 Belledonne Communications SARL, All rights reserved.

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

Route::get('/', 'AccountController@home')->name('account.home');
Route::get('terms', 'AccountController@terms')->name('account.terms');

Route::get('login', 'AccountAuthenticateController@login')->name('account.login');
Route::post('authenticate', 'AccountAuthenticateController@authenticate')->name('account.authenticate');

Route::get('login/email', 'AccountAuthenticateController@loginEmail')->name('account.login_email');
Route::post('authenticate/email', 'AccountAuthenticateController@authenticateEmail')->name('account.authenticate.email');
Route::get('authenticate/email/{code}', 'AccountAuthenticateController@authenticateEmailConfirm')->name('account.authenticate.email_confirm');

Route::get('login/phone', 'AccountAuthenticateController@loginPhone')->name('account.login_phone');
Route::post('authenticate/phone', 'AccountAuthenticateController@authenticatePhone')->name('account.authenticate.phone');
Route::post('authenticate/phone/confirm', 'AccountAuthenticateController@authenticatePhoneConfirm')->name('account.authenticate.phone_confirm');

Route::get('register', 'AccountRegisterController@register')->name('account.register');

if (config('app.phone_authentication')) {
    Route::get('register/phone', 'AccountRegisterController@registerPhone')->name('account.register.phone');
    Route::post('register/phone', 'AccountRegisterController@storePhone')->name('account.store.phone');
}

Route::get('register/email', 'AccountRegisterController@registerEmail')->name('account.register.email');
Route::post('register/email', 'AccountRegisterController@storeEmail')->name('account.store.email');

Route::group(['middleware' => 'auth'], function () {
    Route::get('panel', 'AccountController@panel')->name('account.panel');
    Route::get('logout', 'AccountAuthenticateController@logout')->name('account.logout');

    Route::get('delete', 'AccountController@delete')->name('account.delete');
    Route::delete('delete', 'AccountController@destroy')->name('account.destroy');

    Route::get('email', 'AccountEmailController@show')->name('account.email');
    Route::post('email', 'AccountEmailController@update')->name('account.email.update');
    Route::get('password', 'AccountPasswordController@show')->name('account.password');
    Route::post('password', 'AccountPasswordController@update')->name('account.password.update');

    Route::get('devices', 'Account\DeviceController@index')->name('account.device.index');
    Route::get('devices/delete/{id}', 'Account\DeviceController@delete')->name('account.device.delete');
    Route::delete('devices/{id}', 'Account\DeviceController@destroy')->name('account.device.destroy');
});

Route::group(['middleware' => 'auth.admin'], function () {
    Route::get('admin/accounts/{search?}', 'Admin\AccountController@index')->name('admin.account.index');
    Route::post('admin/search', 'Admin\AccountController@search')->name('admin.account.search');

    Route::get('admin/accounts/show/{id}', 'Admin\AccountController@show')->name('admin.account.show');
    Route::get('admin/accounts/{id}/activate', 'Admin\AccountController@activate')->name('admin.account.activate');
    Route::get('admin/accounts/{id}/deactivate', 'Admin\AccountController@deactivate')->name('admin.account.deactivate');
    Route::get('admin/accounts/{id}/admin', 'Admin\AccountController@admin')->name('admin.account.admin');
    Route::get('admin/accounts/{id}/unadmin', 'Admin\AccountController@unadmin')->name('admin.account.unadmin');
});