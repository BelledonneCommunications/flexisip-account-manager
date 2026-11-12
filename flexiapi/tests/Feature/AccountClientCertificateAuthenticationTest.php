<?php

namespace Tests\Feature;

use App\Account;
use App\Space;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class AccountClientCertificateAuthenticationTest extends TestCase
{
    protected $route = '/provisioning/me';
    protected $wrongRoute = 'api/accounts/me';
    protected $method = 'GET';

    public function testClientCertificateAuthenticationFlow(): void
    {
        $account = Account::factory()->create();
        $config = [
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];
        $expirationDays = 365;

        // Generate private key
        $serverPrivateKey = openssl_pkey_new($config);
        $clientPrivateKey = openssl_pkey_new($config);

        // Generate signature request
        $serverCsrRequest = openssl_csr_new(['CN' => 'server_cert'], $serverPrivateKey, $config);
        $serverCsr = openssl_csr_sign($serverCsrRequest, null, $serverPrivateKey, $expirationDays, $config);

        $clientCsrRequest = openssl_csr_new(["CN" => $account->sip_uri], $clientPrivateKey, $config);
        $clientCsr = openssl_csr_sign($clientCsrRequest, $serverCsr, $serverPrivateKey, $expirationDays, $config);

        openssl_x509_export($clientCsr, $csrOut);

        $this->provisioningRequest()
            ->get($this->route)
            ->assertUnauthorized();

        $this->flushSession();
        Auth::logout();

        $this->provisioningRequest()
            ->withServerVariables([
                'SSL_CLIENT_CERT' => $csrOut
            ])->get($this->route)
            ->assertForbidden();

        Space::where('domain', $account->domain)->update(['client_certificate_authentication' => true]);

        $this->provisioningRequest()
            ->withServerVariables([
                'SSL_CLIENT_CERT' => $csrOut
            ])->get($this->route)
            ->assertOk();
        $this->assertAuthenticatedAs($account);

        $this->flushSession();
        Auth::logout();

        $this->provisioningRequest()
            ->withServerVariables([
                'SSL_CLIENT_CERT' => $csrOut
            ])->get($this->wrongRoute)
            ->assertUnauthorized();

        // New cartificate with the wrong CN

        $clientCsrRequest = openssl_csr_new(["CN" => 'sip:notauser@sip.example.com'], $clientPrivateKey, $config);
        $clientCsr = openssl_csr_sign($clientCsrRequest, $serverCsr, $serverPrivateKey, $expirationDays, $config);

        openssl_x509_export($clientCsr, $csrOut);

        $this->provisioningRequest()
            ->withServerVariables([
                'SSL_CLIENT_CERT' => $csrOut
            ])->get($this->route)
            ->assertForbidden();
    }

    private function provisioningRequest()
    {
        return $this->withHeaders([
            'x-linphone-provisioning' => true
        ]);
    }
}
