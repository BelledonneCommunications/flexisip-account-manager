<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use CoderCat\JWKToPEM\JWKConverter;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SpaceSsoServer extends Model
{
    use HasFactory;

    protected $hidden = ['space_id', 'client_secret'];
    protected $fillable = ['server_url', 'realm', 'sip_identifier', 'client_id', 'client_secret', 'role_provisioning'];
    protected $casts = [
        'auto_provisioning' => 'boolean',
    ];

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function refreshSSOCertificate(): bool
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
                // Something bad happened during the query
            }
        }

        return false;
    }
}
