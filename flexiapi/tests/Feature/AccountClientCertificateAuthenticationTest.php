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

    public function testClientCertificateAuthenticationSubjectAltNameFlow(): void
    {
        $account = Account::factory()->create();

        $configFile = <<<CNF
[req]
distinguished_name = dn

[dn]
CN = wrong

[v3_req]
subjectAltName = URI:$account->sip_uri
CNF;

        $tempFile = tempnam(sys_get_temp_dir(), 'temp');
        file_put_contents($tempFile, $configFile);

        $options = [
            'config' => $tempFile,
            'x509_extensions' => 'v3_req',
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $csrOut = $this->generateCertificate(config: $options, cnSipUri: 'wrong');

        unlink($tempFile);

        $parsed = openssl_x509_parse($csrOut);
        $this->assertStringContainsString('URI:' . $account->sip_uri, $parsed['extensions']['subjectAltName']);

        Space::where('domain', $account->domain)->update(['client_certificate_authentication' => true]);

        $this->provisioningRequest()
            ->withServerVariables([
                'SSL_CLIENT_CERT' => $csrOut
            ])->get($this->route)
            ->assertOk();
        $this->assertAuthenticatedAs($account);
    }

    public function testClientCertificateAuthenticationCNFlow(): void
    {
        $account = Account::factory()->create();
        $config = [
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];
        $csrOut = $this->generateCertificate(config: $config, cnSipUri: $account->sip_uri);

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

        // New certificate with the wrong CN

        $csrOut = $this->generateCertificate(config: $config, cnSipUri: 'sip:notauser@sip.example.com');

        $this->provisioningRequest()
            ->withServerVariables([
                'SSL_CLIENT_CERT' => $csrOut
            ])->get($this->route)
            ->assertForbidden();
    }

    private function generateCertificate(array $config, string $cnSipUri): string
    {
        $expirationDays = 365;

        // Generate private key
        $serverPrivateKey = openssl_pkey_new($config);
        $clientPrivateKey = openssl_pkey_new($config);

        // Generate signature request
        $serverCsrRequest = openssl_csr_new(['CN' => 'server_cert'], $serverPrivateKey, $config);
        $serverCsr = openssl_csr_sign($serverCsrRequest, null, $serverPrivateKey, $expirationDays, $config);

        $clientCsrRequest = openssl_csr_new(["CN" => $cnSipUri], $clientPrivateKey, $config);
        $clientCsr = openssl_csr_sign($clientCsrRequest, $serverCsr, $serverPrivateKey, $expirationDays, $config);

        openssl_x509_export($clientCsr, $csrOut);

        return $csrOut;
    }

    private function provisioningRequest()
    {
        return $this->withHeaders([
            'x-linphone-provisioning' => true
        ]);
    }
}
