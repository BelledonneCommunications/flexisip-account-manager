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

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\ApiKeyController;
use App\Http\Controllers\Account\AuthenticateController;
use App\Http\Controllers\Account\AuthTokenController;
use App\Http\Controllers\Account\ContactVcardController;
use App\Http\Controllers\Account\CreationRequestTokenController;
use App\Http\Controllers\Account\DeviceController;
use App\Http\Controllers\Account\EmailController;
use App\Http\Controllers\Account\FileController;
use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Account\PhoneController;
use App\Http\Controllers\Account\ProvisioningController;
use App\Http\Controllers\Account\RecoveryController;
use App\Http\Controllers\Account\RegisterController;
use App\Http\Controllers\Account\VcardsStorageController;
use App\Http\Controllers\Admin\Account\AccountTypeController;
use App\Http\Controllers\Admin\Account\ActionController;
use App\Http\Controllers\Admin\Account\ActivityController;
use App\Http\Controllers\Admin\Account\CardDavCredentialsController;
use App\Http\Controllers\Admin\Account\ContactController;
use App\Http\Controllers\Admin\Account\DeviceController as AdminAccountDeviceController;
use App\Http\Controllers\Admin\Account\DictionaryController;
use App\Http\Controllers\Admin\Account\FileController as AdminFileController;
use App\Http\Controllers\Admin\Account\ImportController;
use App\Http\Controllers\Admin\Account\StatisticsController as AdminAccountStatisticsController;
use App\Http\Controllers\Admin\Account\TypeController;
use App\Http\Controllers\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Admin\ApiKeyController as AdminApiKeyController;
use App\Http\Controllers\Admin\ExternalAccountController;
use App\Http\Controllers\Admin\PhoneCountryController;
use App\Http\Controllers\Admin\ProvisioningEmailController;
use App\Http\Controllers\Admin\ResetPasswordEmailController;
use App\Http\Controllers\Admin\Space\CardDavServerController;
use App\Http\Controllers\Admin\Space\ContactsListContactController;
use App\Http\Controllers\Admin\Space\ContactsListController;
use App\Http\Controllers\Admin\Space\EmailServerController;
use App\Http\Controllers\Admin\SpaceController;
use App\Http\Controllers\Admin\StatisticsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'login')->name('account.home');
Route::get('about', [AboutController::class, 'about'])->name('about');

Route::middleware(['feature.web_panel_enabled'])->group(function () {
    Route::get('wizard/{provisioning_token}', [ProvisioningController::class, 'wizard'])->name('provisioning.wizard');

    Route::get('login', [AuthenticateController::class, 'login'])->name('account.login');
    Route::post('authenticate', [AuthenticateController::class, 'authenticate'])->name('account.authenticate');
    Route::get('authenticate/qrcode/{token?}', [AuthenticateController::class, 'loginAuthToken'])->name('account.authenticate.auth_token');
    Route::get('logout', [AuthenticateController::class, 'logout'])->name('account.logout');

    Route::get('reset_password/{token}', [ResetPasswordEmailController::class, 'change'])->name('account.reset_password_email.change');
    Route::post('reset_password', [ResetPasswordEmailController::class, 'reset'])->name('account.reset_password_email.reset');

    Route::prefix('creation_token')->controller(CreationRequestTokenController::class)->group(function () {
        Route::get('check/{token}', 'check')->name('account.creation_request_token.check');
        Route::post('validate', 'validateToken')->name('account.creation_request_token.validate');
    });
});

Route::name('file.')->prefix('files')->controller(FileController::class)->group(function () {
    Route::get('{uuid}/{name}', 'show')->name('show');
    Route::get('{uuid}/{name}/download', 'download')->name('download');
});

Route::group(['middleware' => ['auth.jwt', 'auth.digest_or_key']], function () {

    Route::get('provisioning/me', [ProvisioningController::class, 'me'])->name('provisioning.me');

    // vCard 4.0
    Route::get('contacts/vcard/{sip}', [ContactVcardController::class, 'show'])->name('account.contacts.vcard.show');
    Route::get('contacts/vcard', [ContactVcardController::class, 'index'])->name('account.contacts.vcard.index');

    // vCards Storage
    Route::get('vcards-storage/{uuid}', [VcardsStorageController::class, 'show'])->name('account.vcards-storage.show');
    Route::get('vcards-storage/', [VcardsStorageController::class, 'index'])->name('account.vcards-storage.index');
});

Route::name('provisioning.')->prefix('provisioning')->controller(ProvisioningController::class)->group(function () {
    Route::get('documentation', 'documentation')->name('documentation');
    Route::get('auth_token/{auth_token}', 'authToken')->name('auth_token');
    Route::get('qrcode/{provisioning_token}', 'qrcode')->name('qrcode');
    Route::get('{provisioning_token}', 'provision')->name('provision');
    Route::get('/', 'show')->name('show');
});

Route::middleware(['feature.web_panel_enabled'])->group(function () {
    Route::middleware(['feature.public_registration'])->group(function () {
        Route::redirect('register', 'register/email')->name('account.register');

        Route::middleware(['feature.phone_registration'])->group(function () {
            Route::get('register/phone', [RegisterController::class, 'registerPhone'])->name('account.register.phone');
        });

        Route::get('register/email', [RegisterController::class, 'registerEmail'])->name('account.register.email');
        Route::post('accounts', [AccountController::class, 'store'])->name('account.store');
    });

    Route::prefix('recovery')->controller(RecoveryController::class)->group(function () {
        Route::get('phone/{account_recovery_token}', 'showPhone')->name('account.recovery.show.phone');
        Route::get('email', 'showEmail')->name('account.recovery.show.email');
        Route::post('/', 'send')->name('account.recovery.send');
        Route::post('confirm', 'confirm')->name('account.recovery.confirm');
    });

    Route::name('account.')->middleware(['auth', 'auth.check_blocked'])->group(function () {
        Route::get('blocked', [AccountController::class, 'blocked'])->name('blocked');

        Route::prefix('email')->controller(EmailController::class)->group(function () {
            Route::get('change', 'change')->name('email.change');
            Route::post('change', 'requestChange')->name('email.request_change');
            Route::get('validate', 'validateChange')->name('email.validate');
            Route::post('/', 'store')->name('email.update');
        });

        Route::middleware(['feature.phone_registration'])->group(function () {
            Route::prefix('phone')->controller(PhoneController::class)->group(function () {
                Route::get('change', 'change')->name('phone.change');
                Route::post('change', 'requestChange')->name('phone.request_change');
                Route::get('validate', 'validateChange')->name('phone.validate');
                Route::post('/', 'store')->name('phone.update');
            });
        });

        Route::name('device.')->prefix('devices')->controller(DeviceController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('{device_id}/delete', 'delete')->name('delete');
            Route::delete('/', 'destroy')->name('destroy');
        });

        Route::controller(AccountController::class)->group(function () {
            Route::get('dashboard', 'dashboard')->name('dashboard');

            Route::get('delete', 'delete')->name('delete');
            Route::delete('delete', 'destroy')->name('destroy');
        });

        Route::name('password.')->prefix('password')->controller(PasswordController::class)->group(function () {
            Route::get('/', 'show')->name('show');
            Route::post('/', 'update')->name('update');
        });

        Route::name('api_keys.')->prefix('api_key')->controller(ApiKeyController::class)->group(function () {
            Route::get('/', 'show')->name('show');
            Route::post('/', 'update')->name('update');
        });

        Route::post('auth_tokens', [AuthTokenController::class, 'create'])->name('auth_tokens.create');
        Route::get('auth_tokens/auth/external/{token}', [AuthTokenController::class, 'authExternal'])->name('auth_tokens.auth.external');
    });

    Route::get('auth_tokens/qrcode/{token}', [AuthTokenController::class, 'qrcode'])->name('auth_tokens.qrcode');
    Route::get('auth_tokens/auth/{token}', [AuthTokenController::class, 'auth'])->name('auth_tokens.auth');

    Route::name('admin.')->prefix('admin')->middleware(['auth.admin', 'auth.check_blocked'])->group(function () {
        Route::name('spaces.')->prefix('spaces')->group(function () {
            Route::get('me', [SpaceController::class, 'me'])->name('me');
            Route::get('{space}/configuration', [SpaceController::class, 'configuration'])->name('configuration');
            Route::put('{space}/configuration', [SpaceController::class, 'configurationUpdate'])->name('configuration.update');
            Route::get('{space}/integration', [SpaceController::class, 'integration'])->name('integration');

            Route::name('email.')->prefix('{space}/email')->controller(EmailServerController::class)->group(function () {
                Route::get('/', 'show')->name('show');
                Route::post('/', 'store')->name('store');
                Route::get('delete', 'delete')->name('delete');
                Route::delete('/', 'destroy')->name('destroy');
            });
            Route::resource('{space}/carddavs', CardDavServerController::class, ['except' => ['index', 'show']]);
            Route::get('{space}/carddavs/{carddav}/delete', [CardDavServerController::class, 'delete'])->name('carddavs.delete');

            Route::name('contacts_lists.')->prefix('{space}/contacts_lists')->controller(ContactsListController::class)->group(function () {
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

        Route::name('api_keys.')->prefix('api_keys')->controller(AdminApiKeyController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{key}/delete', 'delete')->name('delete');
            Route::delete('/', 'destroy')->name('destroy');
        });

        Route::middleware(['auth.super_admin'])->group(function () {
            Route::resource('spaces', SpaceController::class);
            Route::get('spaces/delete/{id}', [SpaceController::class, 'delete'])->name('spaces.delete');

            Route::get('spaces/{space}/administration', [SpaceController::class, 'administration'])->name('spaces.administration');
            Route::put('spaces/{space}/administration', [SpaceController::class, 'administrationUpdate'])->name('spaces.administration.update');

            Route::name('phone_countries.')->controller(PhoneCountryController::class)->prefix('phone_countries')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/activate_all', 'activateAll')->name('activate_all');
                Route::get('/deactivate_all', 'deactivateAll')->name('deactivate_all');
                Route::get('/{code}/activate', 'activate')->name('activate');
                Route::get('/{code}/deactivate', 'deactivate')->name('deactivate');
            });
        });

        Route::name('statistics.')->controller(StatisticsController::class)->prefix('statistics')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('call_logs', 'editCallLogs')->name('edit_call_logs');
            Route::get('call_logs', 'showCallLogs')->name('show_call_logs');
            Route::get('/{type?}', 'show')->name('show');
            Route::post('/', 'edit')->name('edit');
            //Route::post('search', 'search')->name('search');
        });

        Route::name('account.')->prefix('accounts')->group(function () {
            Route::name('import.')->prefix('import')->controller(ImportController::class)->group(function () {
                Route::get('/', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::post('handle', 'handle')->name('handle');
            });

            Route::middleware(['feature.intercom'])->group(function () {
                Route::name('type.')->prefix('types')->controller(TypeController::class)->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('create', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get('{type_id}/edit', 'edit')->name('edit');
                    Route::put('{type_id}', 'update')->name('update');
                    Route::get('{type_id}/delete', 'delete')->name('delete');
                    Route::delete('{type_id}', 'destroy')->name('destroy');
                });

                Route::name('account_type.')->prefix('{account}/types')->controller(AccountTypeController::class)->group(function () {
                    Route::get('create', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::delete('{type_id}', 'destroy')->name('destroy');
                });

                Route::name('action.')->prefix('{account}/actions')->controller(ActionController::class)->group(function () {
                    Route::get('create', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get('{action_id}/edit', 'edit')->name('edit');
                    Route::put('{action_id}', 'update')->name('update');
                    Route::get('{action_id}/delete', 'delete')->name('delete');
                    Route::delete('{action_id}', 'destroy')->name('destroy');
                });
            });

            Route::controller(AdminAccountController::class)->group(function () {
                Route::get('{account_id}/provision', 'provision')->name('provision');

                Route::get('create', 'create')->name('create');
                Route::get('{account_id}', 'show')->name('show');
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

            Route::name('reset_password_email.')->controller(ResetPasswordEmailController::class)->prefix('{account_id}/reset_password_email')->group(function () {
                Route::get('create', 'create')->name('create');
                Route::get('send', 'send')->name('send');
            });

            Route::name('provisioning_email.')->controller(ProvisioningEmailController::class)->prefix('{account_id}/provisioning_email')->group(function () {
                Route::get('create', 'create')->name('create');
                Route::get('send', 'send')->name('send');
            });

            Route::name('contact.')->prefix('{account}/contacts')->controller(ContactController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('{contact_id}/delete', 'delete')->name('delete');
                Route::delete('/', 'destroy')->name('destroy');
            });

            Route::name('device.')->prefix('{account}/devices')->controller(AdminAccountDeviceController::class)->group(function () {
                Route::get('{device_id}/delete', 'delete')->name('delete');
                Route::delete('/', 'destroy')->name('destroy');
            });

            Route::resource('{account}/carddavs', CardDavCredentialsController::class, ['only' => ['create', 'store', 'destroy']]);
            Route::get('{account}/carddavs/{carddav}/delete', [CardDavCredentialsController::class, 'delete'])->name('carddavs.delete');

            Route::name('dictionary.')->prefix('{account}/dictionary')->controller(DictionaryController::class)->group(function () {
                Route::get('create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('{entry}/edit', 'edit')->name('edit');
                Route::put('{entry}', 'update')->name('update');
                Route::get('{key}/delete', 'delete')->name('delete');
                Route::delete('/', 'destroy')->name('destroy');
            });

            Route::name('external.')->prefix('{account}/external')->controller(ExternalAccountController::class)->group(function () {
                Route::get('/', 'show')->name('show');
                Route::post('/', 'store')->name('store');
                Route::get('delete', 'delete')->name('delete');
                Route::delete('/', 'destroy')->name('destroy');
            });

            Route::name('activity.')->prefix('{account}/activity')->controller(ActivityController::class)->group(function () {
                Route::get('/', 'index')->name('index');
            });

            Route::name('statistics.')->prefix('{account}/statistics')->controller(AdminAccountStatisticsController::class)->group(function () {
                Route::get('/', 'show')->name('show');
                Route::post('call_logs', 'editCallLogs')->name('edit_call_logs');
                Route::get('call_logs', 'showCallLogs')->name('show_call_logs');
                Route::post('/', 'edit')->name('edit');
            });

            Route::name('file.')->prefix('{account}/files')->controller(AdminFileController::class)->group(function () {
                Route::get('{file_id}/delete', 'delete')->name('delete');
                Route::delete('{file_id}', 'destroy')->name('destroy');
            });
        });
    });
});
