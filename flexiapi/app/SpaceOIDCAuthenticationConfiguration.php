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

    public ?string $refreshKeysErrors = null;

    protected $table = 'space_oidc_authentication_configurations';
    protected $hidden = ['space_id', 'client_id', 'client_secret', 'id', 'created_at', 'updated_at'];
    protected $fillable = ['authorization_server', 'audience', 'sip_id_claim', 'client_id', 'client_secret'];

    private array $resolvedKeys = [];

    public function save(array $options = [])
    {
        parent::save($options);

        foreach ($this->resolvedKeys as $key) {
            $key->oidc_id = $this->attributes['id'];
            $key->save();
        }
    }

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function keys()
    {
        return $this->hasMany(SpaceOIDCAuthenticationConfigurationKey::class, 'oidc_id', 'id');
    }

    public function refreshOIDCCertificate(): int
    {
        if ($this->authorization_server) {
            try {
                $response = Http::get($this->authorization_server . '.well-known/openid-configuration');

                if ($response->status() == '200' && $jwksURI = $response->json('jwks_uri')) {
                    $response = Http::get($jwksURI);
                    $jwkConverter = new JWKConverter;

                    if ($response->status() == '200' && $publicKeys = $response->json('keys')) {
                        $this->keys()->delete();

                        $count = 0;

                        foreach ($publicKeys as $publicKey) {
                            if ($publicKey['use'] == 'sig') {
                                $key = new SpaceOIDCAuthenticationConfigurationKey;
                                $key->kid = (string) $publicKey['kid'];
                                $key->public_key = $jwkConverter->toPEM($publicKey);

                                if (isset($this->attributes['id'])) {
                                    $key->oidc_id = $this->attributes['id'];
                                    $key->save();
                                } else {
                                    array_push($this->resolvedKeys, $key);
                                }
                            }

                            $count++;
                        }

                        $this->attributes['updated_at'] = Carbon::now();

                        return $count;
                    }
                }
            } catch (\Throwable $th) {
                $this->refreshKeysErrors = $th->getMessage();
                // Something bad happened during the query
            }
        }

        return 0;
    }
}
