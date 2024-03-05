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
use App\Http\Controllers\Account\ApiKeyController;
use App\Http\Controllers\Account\CreationRequestTokenController;
use App\Http\Controllers\Account\DeviceController;
use App\Http\Controllers\Account\EmailController;
use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Account\PhoneController;
use App\Http\Controllers\Account\ProvisioningController;
use App\Http\Controllers\Account\RecoveryController;
use App\Http\Controllers\Admin\AccountAccountTypeController;
use App\Http\Controllers\Admin\AccountActionController;
use App\Http\Controllers\Admin\AccountActivityController;
use App\Http\Controllers\Admin\AccountContactController;
use App\Http\Controllers\Admin\AccountDeviceController;
use App\Http\Controllers\Admin\AccountDictionaryController;
use App\Http\Controllers\Admin\AccountImportController;
use App\Http\Controllers\Admin\AccountTypeController;
use App\Http\Controllers\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Admin\AccountStatisticsController;
use App\Http\Controllers\Admin\ContactsListController;
use App\Http\Controllers\Admin\ContactsListContactController;
use App\Http\Controllers\Admin\StatisticsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'login')->name('account.home');
Route::get('documentation', 'Account\AccountController@documentation')->name('account.documentation');
Route::get('about', 'AboutController@about')->name('about');

Route::middleware(['web_panel_enabled'])->group(function () {
    Route::get('login', 'Account\AuthenticateController@login')->name('account.login');
    Route::post('authenticate', 'Account\AuthenticateController@authenticate')->name('account.authenticate');
    Route::get('authenticate/qrcode/{token?}', 'Account\AuthenticateController@loginAuthToken')->name('account.authenticate.auth_token');

    Route::prefix('creation_token')->controller(CreationRequestTokenController::class)->group(function () {
        Route::get('check/{token}', 'check')->name('account.creation_request_token.check');
        Route::post('validate', 'validateToken')->name('account.creation_request_token.validate');
    });
});

Route::group(['middleware' => 'auth.digest_or_key'], function () {
    Route::get('provisioning/me', 'Account\ProvisioningController@me')->name('provisioning.me');

    // Vcard 4.0
    Route::get('contacts/vcard/{sip}', 'Account\ContactVcardController@show')->name('account.contacts.vcard.show');
    Route::get('contacts/vcard', 'Account\ContactVcardController@index')->name('account.contacts.vcard.index');
});

Route::name('provisioning.')->prefix('provisioning')->controller(ProvisioningController::class)->group(function () {
    Route::get('documentation', 'documentation')->name('documentation');
    Route::get('auth_token/{auth_token}', 'authToken')->name('auth_token');
    Route::get('qrcode/{provisioning_token}', 'qrcode')->name('qrcode');
    Route::get('{provisioning_token}', 'provision')->name('provision');
    Route::get('/', 'show')->name('show');
});

Route::middleware(['web_panel_enabled'])->group(function () {
    if (config('app.public_registration')) {
        Route::redirect('register', 'register/email')->name('account.register');

        if (config('app.phone_authentication')) {
            Route::get('register/phone', 'Account\RegisterController@registerPhone')->name('account.register.phone');
        }

        Route::get('register/email', 'Account\RegisterController@registerEmail')->name('account.register.email');
        Route::post('accounts', 'Account\AccountController@store')->name('account.store');
    }

    Route::prefix('recovery')->controller(RecoveryController::class)->group(function () {
        Route::get('phone', 'showPhone')->name('account.recovery.show.phone');
        Route::get('email', 'showEmail')->name('account.recovery.show.email');
        Route::post('/', 'send')->name('account.recovery.send');
        Route::post('confirm', 'confirm')->name('account.recovery.confirm');
    });

    Route::middleware(['auth'])->group(function () {
        Route::get('logout', 'Account\AuthenticateController@logout')->name('account.logout');
    });

    Route::name('account.')->middleware(['auth', 'auth.check_blocked'])->group(function () {
        Route::get('blocked', 'Account\AccountController@blocked')->name('blocked');

        Route::prefix('email')->controller(EmailController::class)->group(function () {
            Route::get('change', 'change')->name('email.change');
            Route::post('change', 'requestChange')->name('email.request_change');
            Route::get('validate', 'validateChange')->name('email.validate');
            Route::post('/', 'store')->name('email.update');
        });

        Route::prefix('phone')->controller(PhoneController::class)->group(function () {
            Route::get('change', 'change')->name('phone.change');
            Route::post('change', 'requestChange')->name('phone.request_change');
            Route::get('validate', 'validateChange')->name('phone.validate');
            Route::post('/', 'store')->name('phone.update');
        });

        Route::name('device.')->prefix('devices')->controller(DeviceController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('{device_id}/delete', 'delete')->name('delete');
            Route::delete('/', 'destroy')->name('destroy');
        });

        Route::controller(AccountController::class)->group(function () {
            Route::get('dashboard', 'panel')->name('dashboard');

            Route::get('delete', 'delete')->name('delete');
            Route::delete('delete', 'destroy')->name('destroy');
        });

        Route::prefix('password')->controller(PasswordController::class)->group(function () {
            Route::get('/', 'show')->name('password.show');
            Route::post('/', 'update')->name('password.update');
        });

        Route::prefix('api_key')->controller(ApiKeyController::class)->group(function () {
            Route::get('/', 'show')->name('api_key.show');
            Route::post('/', 'update')->name('api_key.update');
        });

        Route::post('auth_tokens', 'Account\AuthTokenController@create')->name('auth_tokens.create');
        Route::get('auth_tokens/auth/external/{token}', 'Account\AuthTokenController@authExternal')->name('auth_tokens.auth.external');
    });

    Route::get('auth_tokens/qrcode/{token}', 'Account\AuthTokenController@qrcode')->name('auth_tokens.qrcode');
    Route::get('auth_tokens/auth/{token}', 'Account\AuthTokenController@auth')->name('auth_tokens.auth');

    Route::name('admin.')->prefix('admin')->middleware(['auth.admin', 'auth.check_blocked'])->group(function () {
        Route::name('statistics.')->controller(StatisticsController::class)->prefix('statistics')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('call_logs', 'editCallLogs')->name('edit_call_logs');
            Route::get('call_logs', 'showCallLogs')->name('show_call_logs');
            Route::get('/{type?}', 'show')->name('show');
            Route::post('/', 'edit')->name('edit');
            //Route::post('search', 'search')->name('search');
        });

        Route::name('account.')->prefix('accounts')->group(function () {
            Route::controller(AdminAccountController::class)->group(function () {
                Route::get('{account_id}/provision', 'provision')->name('provision');

                Route::get('create', 'create')->name('create');
                Route::post('accounts', 'store')->name('store');

                Route::get('{account_id}/edit', 'edit')->name('edit');
                Route::put('{account_id}', 'update')->name('update');

                Route::get('{account_id}/delete', 'delete')->name('delete');
                Route::delete('/', 'destroy')->name('destroy');

                Route::get('/', 'index')->name('index');
                Route::post('search', 'search')->name('search');

                Route::get('{account_id}/contacts_lists/detach', 'contactsListRemove')->name('contacts_lists.detach');
                Route::post('{account_id}/contacts_lists', 'contactsListAdd')->name('contacts_lists.attach');
            });

            Route::name('import.')->prefix('import')->controller(AccountImportController::class)->group(function () {
                Route::get('/', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::post('handle', 'handle')->name('handle');
            });

            if (config('app.intercom_features')) {
                Route::name('type.')->prefix('types')->controller(AccountTypeController::class)->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('create', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get('{type_id}/edit', 'edit')->name('edit');
                    Route::put('{type_id}', 'update')->name('update');
                    Route::get('{type_id}/delete', 'delete')->name('delete');
                    Route::delete('{type_id}', 'destroy')->name('destroy');
                });

                Route::name('account_type.')->prefix('{account}/types')->controller(AccountAccountTypeController::class)->group(function () {
                    Route::get('create', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::delete('{type_id}', 'destroy')->name('destroy');
                });

                Route::name('action.')->prefix('{account}/actions')->controller(AccountActionController::class)->group(function () {
                    Route::get('create', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get('{action_id}/edit', 'edit')->name('edit');
                    Route::put('{action_id}', 'update')->name('update');
                    Route::get('{action_id}/delete', 'delete')->name('delete');
                    Route::delete('{action_id}', 'destroy')->name('destroy');
                });
            }

            Route::name('contact.')->prefix('{account}/contacts')->controller(AccountContactController::class)->group(function () {
                Route::get('create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('{contact_id}/delete', 'delete')->name('delete');
                Route::delete('/', 'destroy')->name('destroy');
            });

            Route::name('device.')->prefix('{account}/devices')->controller(AccountDeviceController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('{device_id}/delete', 'delete')->name('delete');
                Route::delete('/', 'destroy')->name('destroy');
            });

            Route::name('dictionary.')->prefix('{account}/dictionary')->controller(AccountDictionaryController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('{entry}/edit', 'edit')->name('edit');
                Route::put('{entry}', 'update')->name('update');
                Route::get('{key}/delete', 'delete')->name('delete');
                Route::delete('/', 'destroy')->name('destroy');
            });

            Route::name('activity.')->prefix('{account}/activity')->controller(AccountActivityController::class)->group(function () {
                Route::get('/', 'index')->name('index');
            });

            Route::name('statistics.')->prefix('{account}/statistics')->controller(AccountStatisticsController::class)->group(function () {
                Route::get('/', 'show')->name('show');
                Route::post('call_logs', 'editCallLogs')->name('edit_call_logs');
                Route::get('call_logs', 'showCallLogs')->name('show_call_logs');
                Route::post('/', 'edit')->name('edit');
            });
        });

        Route::name('contacts_lists.')->prefix('contacts_lists')->controller(ContactsListController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::post('{contacts_list_id}/search', 'search')->name('search');
            Route::get('{contacts_list_id}/edit', 'edit')->name('edit');
            Route::put('{contacts_list_id}', 'update')->name('update');
            Route::get('{contacts_list_id}/delete', 'delete')->name('delete');
            Route::delete('{contacts_list_id}', 'destroy')->name('destroy');

            Route::name('contacts.')->prefix('{contacts_list_id}/contacts')->controller(ContactsListContactController::class)->group(function () {
                Route::get('add', 'add')->name('add');
                Route::post('search', 'search')->name('search');
                Route::post('/', 'store')->name('store');
                Route::delete('/', 'destroy')->name('destroy');
            });
        });
    });
});
