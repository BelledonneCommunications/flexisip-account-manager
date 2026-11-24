<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpaceOIDCAuthenticationConfigurationKey extends Model
{
    use HasFactory;

    protected $table = 'space_oidc_authentication_configurations_keys';

    public function oidcAuthenticationConfiguration()
    {
        return $this->belongsTo(SpaceOIDCAuthenticationConfiguration::class, 'id', 'oidc_id');
    }
}
