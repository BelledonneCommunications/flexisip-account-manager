<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use CoderCat\JWKToPEM\JWKConverter;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SpaceOIDCAuthenticationConfiguration extends Model
{
    use HasFactory;

    public ?string $refreshSsoError = null;

    protected $table = 'space_oidc_authentication_configurations';
    protected $hidden = ['space_id', 'client_secret', 'id', 'created_at', 'updated_at'];
    protected $fillable = ['server_url', 'realm', 'sip_identifier', 'client_id', 'client_secret', 'role_provisioning'];
    protected $casts = [
        'auto_provisioning' => 'boolean',
    ];

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function refreshOIDCCertificate(): bool
    {
        if ($this->server_url) {
            try {
                $response = Http::get($this->server_url . '/realms/' . $this->realm . '/protocol/openid-connect/certs');
                $jwkConverter = new JWKConverter;

                if ($response->status() == '200' && $publicKey = $response->json('keys')[0]) {
                    $this->public_key = $jwkConverter->toPEM($publicKey);
                    $this->attributes['updated_at'] = Carbon::now();

                    return true;
                }
            } catch (\Throwable $th) {
                $this->refreshSsoError = $th->getMessage();
                // Something bad happened during the query
            }
        }

        return false;
    }
}
