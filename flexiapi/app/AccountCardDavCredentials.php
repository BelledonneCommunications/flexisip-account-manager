<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountCardDavCredentials extends Model
{
    protected $table = 'account_carddav_credentials';

    public function cardDavServer()
    {
        return $this->hasOne(SpaceCardDavServer::class, 'id', 'space_carddav_server_id');
    }

    public function getIdentifierAttribute()
    {
        return $this->username . '@' . $this->domain;
    }
}
