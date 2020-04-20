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

Route::get('login', 'AccountController@login')->name('account.login');
Route::post('authenticate', 'AccountController@authenticate')->name('account.authenticate');

Route::get('login/email', 'AccountController@loginEmail')->name('account.login_email');
Route::post('authenticate/email', 'AccountController@authenticateEmail')->name('account.authenticate_email');
Route::get('authenticate/email/{code}', 'AccountController@authenticateEmailConfirm')->name('account.authenticate_email_confirm');

Route::get('login/phone', 'AccountController@loginPhone')->name('account.login_phone');
Route::post('authenticate/phone', 'AccountController@authenticatePhone')->name('account.authenticate_phone');
Route::post('authenticate/phone/confirm', 'AccountController@authenticatePhoneConfirm')->name('account.authenticate_phone_confirm');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', 'AccountController@index')->name('account.index');
    Route::get('logout', 'AccountController@logout')->name('account.logout');

    Route::get('delete', 'AccountController@delete')->name('account.delete');
    Route::delete('delete', 'AccountController@destroy')->name('account.destroy');

    Route::get('email', 'AccountEmailController@show')->name('account.email');
    Route::post('email', 'AccountEmailController@update')->name('account.email.update');
    Route::get('password', 'AccountPasswordController@show')->name('account.password');
    Route::post('password', 'AccountPasswordController@update')->name('account.password.update');
});