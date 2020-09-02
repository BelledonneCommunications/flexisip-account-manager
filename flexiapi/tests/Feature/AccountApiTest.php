<?php

namespace Tests\Feature;

use App\Password;
use App\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AccountApiTest extends TestCase
{
    use RefreshDatabase;

    protected $route = '/api/accounts';
    protected $method = 'POST';

    public function testMandatoryFrom()
    {
        $password = factory(Password::class)->create();
        $response = $this->json($this->method, $this->route);
        $response->assertStatus(422);
    }

    public function testNotAdminForbidden()
    {
        $password = factory(Password::class)->create();
        $response0 = $this->withHeaders([
            'From' => 'sip:'.$password->account->identifier
        ])->json($this->method, $this->route);

        $response1 = $this->withHeaders([
            'From' => 'sip:'.$password->account->identifier,
            'Authorization' => $this->generateDigest($password, $response0),
        ])->json($this->method, $this->route);

        $response1->assertStatus(403);
    }

    public function testAdminOk()
    {
        $admin = factory(Admin::class)->create();
        $password = $admin->account->passwords()->first();

        $response0 = $this->withHeaders([
            'From' => 'sip:'.$password->account->identifier
        ])->json($this->method, $this->route);

        $username = 'foobar';

        $response1 = $this->withHeaders([
            'From' => 'sip:'.$password->account->identifier,
            'Authorization' => $this->generateDigest($password, $response0),
        ])->json($this->method, $this->route, [
            'username' => $username,
            'algorithm' => 'SHA-256',
            'password' => '123456',
        ]);

        $response1
            ->assertStatus(200)
            ->assertJson([
                'id' => 2,
                'username' => $username
            ]);
    }
}
