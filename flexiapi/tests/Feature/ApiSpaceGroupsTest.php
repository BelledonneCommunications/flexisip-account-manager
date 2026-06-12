<?php

namespace Tests\Feature;

use App\Group;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use App\Account;

class ApiSpaceGroupsTest extends TestCase
{
    protected $route = '/api/spaces/';

    public function testGroup(): void
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();

        $name = fake()->name();
        $username = 'support';
        $strategy = 'ring_all';

        $this->route .= $admin->domain . '/groups';

        // Invalide username
        $this->keyAuthenticated($admin)
            ->json('POST', $this->route, [
                'name' => $name,
                'username' => '!' . $name,
                'strategy' => $strategy,
            ])
            ->assertUnprocessable();

        // Already taken username
        $this->keyAuthenticated($admin)
            ->json('POST', $this->route, [
                'name' => $name,
                'username' => $admin->username,
                'strategy' => $strategy,
            ])
            ->assertUnprocessable();

        // Invalid strategy
        $this->keyAuthenticated($admin)
            ->json('POST', $this->route, [
                'name' => $name,
                'username' => $username,
                'strategy' => $strategy . 'all',
            ])
            ->assertUnprocessable();

        // Ok
        $this->keyAuthenticated($admin)
            ->json('POST', $this->route, [
                'name' => $name,
                'username' => $username,
                'strategy' => $strategy,
            ])
            ->assertCreated();

        // Already taken name
        $this->keyAuthenticated($admin)
            ->json('POST', $this->route, [
                'name' => $name,
                'username' => $username,
                'strategy' => $strategy,
            ])
            ->assertUnprocessable();

        // Second Group
        $this->keyAuthenticated($admin)
            ->json('POST', $this->route, [
                'name' => $name . '1',
                'username' => $username . '1',
                'strategy' => $strategy,
            ])
            ->assertCreated();

        // GET all groups
        $this->keyAuthenticated($admin)
            ->json('GET', $this->route)
            ->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->has(2)
            );

        // GET unexisting Group
        $this->keyAuthenticated($admin)
            ->json('GET', $this->route . '/' . 3)
            ->assertNotFound();

        // GET existing Group
        $this->keyAuthenticated($admin)
            ->json('GET', $this->route . '/' . 2)
            ->assertOk();

        // PATCH Already taken name
        $this->keyAuthenticated($admin)
            ->json('PATCH', $this->route . '/' . 2, [
                'name' => $name,
            ])->assertUnprocessable();

        // PATCH invalid strategy
        $this->keyAuthenticated($admin)
            ->json('PATCH', $this->route . '/' . 2, [
                'name' => $name . '2',
                'strategy' => $strategy . 'all'
            ])->assertUnprocessable();

        // PATCH ok
        $this->keyAuthenticated($admin)
            ->json('PATCH', $this->route . '/' . 2, [
                'name' => $name . '2',
                'strategy' => $strategy,
            ])->assertOk();

        // Delete unexisting Group
        $this->keyAuthenticated($admin)
            ->json('DELETE', $this->route . '/' . 3)
            ->assertNotFound();

        // Delete existing group
        $this->keyAuthenticated($admin)
            ->json('DELETE', $this->route . '/' . 2)
            ->assertNoContent();
    }

    public function testAccountGroup()
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();

        $account = Account::factory()->create();

        $this->route .= $account->domain . '/groups';

        $group = $this->keyAuthenticated($admin)
            ->json('POST', $this->route, [
                'name' => fake()->name(),
                'username' => fake()->userName(),
                'strategy' => 'ring_all',
            ])
            ->assertCreated()
            ->json();

        // POST account to group
        $this->keyAuthenticated($admin)
            ->json('POST', $this->route . '/' . $group['id'] . '/accounts/' . $account->id)
            ->assertOk();

        $this->assertEquals(1, Group::find($group['id'])->accounts()->count());

        // Verify no duplicate
        $this->keyAuthenticated($admin)
            ->json('POST', $this->route . '/' . $group['id'] . '/accounts/' . $account->id)
            ->assertOk();

        $this->assertEquals(1, Group::find($group['id'])->accounts()->count());

        // DELETE account to group
        $this->keyAuthenticated($admin)
            ->json('DELETE', $this->route . '/' . $group['id'] . '/accounts/' . $account->id)
            ->assertOk();

        $this->assertEquals(0, Group::find($group['id'])->accounts()->count());
        ;
    }
}
