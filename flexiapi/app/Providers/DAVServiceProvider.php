<?php

namespace App\Providers;

use App\Account;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use LaravelSabre\LaravelSabre;
use Sabre\DAVACL\PrincipalCollection;
//use Sabre\DAVACL\PrincipalBackend\PDO as PrincipalBackend;
use Sabre\CardDAV\Plugin as CardDAVPlugin;
use Sabre\DAV\Browser\Plugin as BrowserPlugin;
//use Sabre\CardDAV\Backend\PDO

class DAVServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        LaravelSabre::nodes(function () {
            return $this->nodes();
        });

        LaravelSabre::plugins(function () {
            return $this->plugins();
        });
    }

    /**
     * List of nodes for DAV Collection.
     */
    private function nodes() : array
    {
        $contactBackend = new ContactBackend;
        $principalBackend = new PrincipalBackend;

        return [
            //new \Sabre\DAVACL\PrincipalCollection($principalBackend),
            new \Sabre\CardDAV\AddressBookRoot($principalBackend, $contactBackend),
            //new PrincipalCollection($contactBackend),
        ];
        //return Account::all()->toArray();
    }

    private function plugins()
    {
        yield new BrowserPlugin();
        yield new CardDAVPlugin();
    }
}
