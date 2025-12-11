<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2025 Belledonne Communications SARL, All rights reserved.

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

namespace Tests\Feature;

use App\Account;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ApiVoicemailTest extends TestCase
{
    protected $route = '/api/accounts/me/voicemails';
    protected $uploadRoute = '/api/files/';

    public function testAccount()
    {
        $account = Account::factory()->create();
        $account->generateUserApiKey();

        $this->keyAuthenticated($account)
            ->json('POST', $this->route, [])
            ->assertJsonValidationErrors(['content_type']);

        $this->keyAuthenticated($account)
            ->json('POST', $this->route, [
                'content_type' => 'image/jpg'
            ])
            ->assertJsonValidationErrors(['content_type']);

        $accountFile = $this->keyAuthenticated($account)
            ->json('POST', $this->route, [
                'content_type' => 'audio/opus'
            ])->assertCreated();

        $uuid = $accountFile->json()['id'];

        $this->keyAuthenticated($account)
            ->get($this->route)
            ->assertJsonFragment(['id' => $uuid]);

        $this->keyAuthenticated($account)
            ->get($this->route . '/' . $uuid)
            ->assertJsonFragment([
                'id' => $uuid,
                'name' => null,
                'size' => null,
                'sip_from' => null,
                'uploaded_at' => null,
            ]);

        $this->keyAuthenticated($account)
            ->delete($this->route . '/' . $uuid)
            ->assertOk();

        $this->keyAuthenticated($account)
            ->get($this->route . '/' . $uuid)
            ->assertNotFound();
    }

    public function testAdmin()
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();

        $account = Account::factory()->create();
        $account->generateUserApiKey();

        $adminRoute = '/api/accounts/' . $account->id . '/voicemails';

        $this->keyAuthenticated($account)
            ->json('POST', $adminRoute, [])
            ->assertForbidden();

        $accountFile = $this->keyAuthenticated($admin)
            ->json('POST', $adminRoute, [
                'content_type' => 'audio/opus'
            ])->assertCreated();

        $uuid = $accountFile->json()['id'];

        $this->keyAuthenticated($admin)
            ->get($adminRoute . '/' . $uuid)
            ->assertJsonFragment(['id' => $uuid]);

        $this->keyAuthenticated($admin)
            ->get($adminRoute . '/' . $uuid)
            ->assertJsonFragment([
                'id' => $uuid
            ]);

        $this->keyAuthenticated($admin)
            ->delete($adminRoute . '/' . $uuid)
            ->assertOk();

        $this->keyAuthenticated($admin)
            ->get($adminRoute . '/' . $uuid)
            ->assertNotFound();
    }

    public function testUpload()
    {
        $account = Account::factory()->create();
        $account->generateUserApiKey();

        $accountFile = $this->keyAuthenticated($account)
            ->json('POST', $this->route, [
                'content_type' => 'audio/opus'
            ])->assertCreated();

        $uuid = $accountFile->json()['id'];

        $this->keyAuthenticated($account)
            ->json('POST', $this->uploadRoute . $uuid, [
                'file' => UploadedFile::fake()->image('photo.jpg')
            ])->assertJsonValidationErrors(['file']);

        $this->keyAuthenticated($account)
            ->json('POST', $this->uploadRoute . $uuid, [
                'file' => UploadedFile::fake()->create('audio.wav', 500, 'audio/wav')
            ])->assertJsonValidationErrors(['file']);

        $file = $this->keyAuthenticated($account)
            ->json('POST', $this->uploadRoute . $uuid, data: [
                'file' => UploadedFile::fake()->create('audio.opus', 500, 'audio/opus')
            ])->assertOk();

        $this->keyAuthenticated($account)
            ->json('POST', $this->uploadRoute . $uuid, data: [
                'file' => UploadedFile::fake()->create('audio.opus', 500, 'audio/opus')
            ])->assertNotFound();

        $this->head($file->json()['download_url'])->dump()->assertOk();

        // Delete the file
        $this->keyAuthenticated($account)
            ->delete($this->route . '/' . $file->json()['id'])
            ->assertOk();

        $this->head($file->json()['download_url'])->assertNotFound();

        /* To try out with a real file
        $accountFile = $this->keyAuthenticated($account)
            ->json('POST', $this->route, [
                'content_type' => 'audio/wav'
            ])->assertCreated();

        $uuid = $accountFile->json()['id'];

        $this->keyAuthenticated($account)
            ->json('POST', $this->uploadRoute . $uuid, data: [
                'file' => new UploadedFile(
                    storage_path("audio.wav"),
                    'audio.wav',
                    test: true,
                )
            ])->assertOk();
        */
    }
}
