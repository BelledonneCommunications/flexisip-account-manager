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
Route::get('accounts/{sip}/info', 'Api\AccountController@info');
Route::post('accounts/{sip}/activate/email', 'Api\AccountController@activateEmail');
Route::post('accounts/{sip}/activate/phone', 'Api\AccountController@activatePhone');

Route::group(['middleware' => ['auth.digest_or_key']], function () {
    Route::get('accounts/me', 'Api\AccountController@show');
    Route::delete('accounts/me', 'Api\AccountController@delete');

    Route::get('accounts/me/devices', 'Api\DeviceController@index');
    Route::delete('accounts/me/devices/{uuid}', 'Api\DeviceController@destroy');

    Route::post('accounts/me/email/request', 'Api\EmailController@requestUpdate');
    Route::post('accounts/me/password', 'Api\PasswordController@update');

    Route::group(['middleware' => ['auth.admin']], function () {
        Route::get('accounts/{id}/activate', 'Api\Admin\AccountController@activate');
        Route::get('accounts/{id}/deactivate', 'Api\Admin\AccountController@deactivate');
        Route::post('accounts', 'Api\Admin\AccountController@store');
        Route::get('accounts', 'Api\Admin\AccountController@index');
        Route::get('accounts/{id}', 'Api\Admin\AccountController@show');
        Route::delete('accounts/{id}', 'Api\Admin\AccountController@destroy');
    });
});