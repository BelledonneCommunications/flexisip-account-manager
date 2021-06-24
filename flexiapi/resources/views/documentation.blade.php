@extends('layouts.main')

@section('content')
<h1>Flexisip API</h1>
<p>An API to deal with the Flexisip server</p>

<p>The API is available under <code>/api</code></p>
<p>A <code>from</code> (consisting of the user SIP address, prefixed with <code>sip:</code>), <code>content-type</code> and <code>accept</code> HTTP headers are required to use the API properly</p>

<pre>
<code>> GET /api/{endpoint}
> from: sip:foobar@sip.example.org
> content-type: application/json
> accept: application/json</code></pre>

<h2 id="authentication"><a href="#authentication">Authentication</a></h2>

<p>Restricted endpoints are protected using a DIGEST authentication or an API Key mechanisms.</p>

<h3 id="authentication_api_key"><a href="#authentication_api_key">Using the API Key</a></h3>

<p>To authenticate using an API Key, you need to <a href="{{ route('account.login') }}">authenticate to your account panel</a> and being an administrator.</p>
<p>On your panel you will then find a form to generate your personnal key.</p>

<p>You can then use your freshly generated key by adding a new <code>x-api-key</code> header to your API requests:</p>

<pre>
    <code>> GET /api/{endpoint}
    > from: sip:foobar@sip.example.org
    > x-api-key: {your-api-key}
    > …</code></pre>

<h3 id="authentication_digest"><a href="#authentication_digest">Using DIGEST</a></h3>

<p>To discover the available hashing algorythm you MUST send an unauthenticated request to one of the restricted endpoints.<br />
For the moment only DIGEST-MD5 and DIGEST-SHA-256 are supported through the authentication layer.</p>

<pre>
<code>> GET /api/{restricted-endpoint}
> …

< HTTP 401
< content-type: application/json
< www-authenticate: Digest realm=test,qop=auth,algorithm=MD5,nonce="{nonce}",opaque="{opaque}"
< www-authenticate: Digest realm=test,qop=auth,algorithm=SHA-256,nonce="{nonce}",opaque="{opaque}"</code></pre>

<p>You can find more documentation on the related <a href="https://tools.ietf.org/html/rfc7616">IETF RFC-7616</a>.</p>

<h2 id="endpoints"><a href="#endpoints">Endpoints</a></h2>

<h3 id="public_endpoints"><a href="#public_endpoints">Public endpoints</a></h3>

<h4><code>GET /ping</code></h4>
<p>Returns <code>pong</code></p>

<h4>Accounts</h4>

<h4><code>POST /tokens</code></h4>
<p>Send a token using a push notification to the device.</p>
<p>Return <code>403</code> if a token was already sent, or if the tokens limit is reached for this device.</p>
<p>Return <code>503</code> if the token was not successfully sent.</p>

<p>JSON parameters:</p>
<ul>
    <li><code>pn_provider</code> the push notification provider</li>
    <li><code>pn_param</code> the push notification parameter</li>
    <li><code>pn_prid</code> the push notification unique id</li>
</ul>

<h4><code>POST /accounts/with-token</code></h4>
<p>Create an account using a token.</p>
<p>Return <code>422</code> if the parameters are invalid or if the token is expired.</p>

<p>JSON parameters:</p>
<ul>
    <li><code>username</code> unique username, minimum 6 characters</li>
    <li><code>password</code> required minimum 6 characters</li>
    <li><code>algorithm</code> required, values can be <code>SHA-256</code> or <code>MD5</code></li>
    <li><code>domain</code> optional, the value is set to the default registration domain if not set</li>
    <li><code>token</code> the unique token</li>
</ul>

<h4><code>GET /accounts/{sip}/info</code></h4>
<p>Retrieve public information about the account.</p>
<p>Return <code>404</code> if the account doesn't exists.</p>

<h4><code>POST /accounts/{sip}/activate/email</code></h4>
<p>Activate an account using a secret code received by email.</p>
<p>Return <code>404</code> if the account doesn't exists or if the code is incorrect, the validated account otherwise.</p>
<p>JSON parameters:</p>
<ul>
    <li><code>code</code> the code</li>
</ul>

<h4><code>POST /accounts/{sip}/activate/phone</code></h4>
<p>Activate an account using a pin code received by phone.</p>
<p>Return <code>404</code> if the account doesn't exists or if the code is incorrect, the validated account otherwise.</p>
<p>JSON parameters:</p>
<ul>
    <li><code>code</code> the PIN code</li>
</ul>

<h3 id="authenticated_endpoints"><a href="#authenticated_endpoints">User authenticated endpoints</a></h3>
<p>Those endpoints are authenticated and requires an activated account.</p>

<h4><code>GET /accounts/me</code></h4>
<p>Retrieve the account information.</p>

<h4><code>DELETE /accounts/me</code></h4>
<p>Delete the account.</p>

<h4><code>POST /accounts/me/email/request</code></h4>
<p>Change the account email. An email will be sent to the new email address to confirm the operation.</p>
<p>JSON parameters:</p>
<ul>
    <li><code>email</code> the new email address</li>
</ul>

<h4><code>POST /accounts/me/password</code></h4>
<p>Change the account password.</p>
<p>JSON parameters:</p>
<ul>
    <li><code>algorithm</code> required, values can be <code>SHA-256</code> or <code>MD5</code></li>
    <li><code>old_password</code> required if the password is already set, the old password</li>
    <li><code>password</code> required, the new password</li>
</ul>

<h4>Phone number</h4>

<h4><code>POST /accounts/me/phone/request</code></h4>
<p>Request a specific code by SMS</p>
<p>JSON parameters:</p>
<ul>
    <li><code>phone</code> the phone number to send the SMS</li>
</ul>

<h4><code>POST /accounts/me/phone</code></h4>
<p>Confirm the code received and change the phone number</p>
<p>JSON parameters:</p>
<ul>
    <li><code>code</code> the received SMS code</li>
</ul>

<p>Return the updated account</p>

<h4>Devices</h4>

<h4><code>GET /accounts/me/devices</code></h4>
<p>Return the user registered devices.</p>

<h4><code>DELETE /accounts/me/devices/{uuid}</code></h4>
<p>Remove one of the user registered devices.</p>

<h3 id="admin_endpoints"><a href="#admin_endpoints">Admin endpoints</a></h3>

<p>Those endpoints are authenticated and requires an admin account.</p>

<h4><code>POST /accounts</code></h4>
<p>To create an account directly from the API.</p>
<p>If <code>activated</code> is set to <code>false</code> a random generated <code>confirmation_key</code> will be returned to allow further activation using the public endpoints. Check <code>confirmation_key_expires</code> to also set an expiration date on that <code>confirmation_key</code>.</p>

<p>JSON parameters:</p>
<ul>
    <li><code>username</code> unique username, minimum 6 characters</li>
    <li><code>password</code> required minimum 6 characters</li>
    <li><code>algorithm</code> required, values can be <code>SHA-256</code> or <code>MD5</code></li>
    <li><code>domain</code> optional, the value is set to the default registration domain if not set</li>
    <li><code>activated</code> optional, a boolean, set to <code>false</code> by default</li>
    <li><code>admin</code> optional, a boolean, set to <code>false</code> by default, create an admin account</li>
    <li><code>phone</code> optional, a phone number, set a phone number to the account</li>
    <li><code>confirmation_key_expires</code> optional, a datetime of this format: Y-m-d H:i:s. Only used when <code>activated</code> is not used or <code>false</code>. Enforces an expiration date on the returned <code>confirmation_key</code>. After that datetime public email or phone activation endpoints will return <code>403</code>.</li>
</ul>

<h4><code>GET /accounts</code></h4>
<p>Retrieve all the accounts, paginated.</p>

<h4><code>GET /accounts/{id}</code></h4>
<p>Retrieve a specific account.</p>

<h4><code>DELETE /accounts/{id}</code></h4>
<p>Delete a specific account and its related information.</p>

<h4><code>GET /accounts/{id}/activate</code></h4>
<p>Activate an account.</p>

<h4><code>GET /accounts/{id}/deactivate</code></h4>
<p>Deactivate an account.</p>

<h2 id="provisioning"><a href="#provisioning">Provisioning</a></h2>

<p>When an account is having an available <code>confirmation_key</code> it can be provisioned using the two following URL.</p>

<p>Those two URL are <b>not API endpoints</b>, they are not located under <code>/api</code>.

<h4><code>VISIT /provisioning/{confirmation_key}</code></h4>
<p>Return the provisioning information available in the liblinphone configuration file (if correctly configured).</p>
<p>If the <code>confirmation_key</code> is valid the related account information are added to the returned XML. The account is then considered as "provisioned" and those account related information will be removed in the upcoming requests.</p>

<h4><code>VISIT /provisioning/qrcode/{confirmation_key}</code></h4>
<p>Return a QRCode that points to the provisioning URL.</p>

@endsection
