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

<h2>Authentication</h2>

<p>Restricted endpoints are protected using a DIGEST authentication or an API Key mechanisms.</p>

<h3>Using the API Key</h3>

<p>To authenticate using an API Key, you need to <a href="{{ route('account.login') }}">authenticate to your account panel</a> and being an administrator.</p>
<p>On your panel you will then find a form to generate your personnal key.</p>

<p>You can then use your freshly generated key by adding a new <code>x-api-key</code> header to your API requests:</p>

<pre>
    <code>> GET /api/{endpoint}
    > from: sip:foobar@sip.example.org
    > x-api-key: {your-api-key}
    > …</code></pre>

<h3>Using DIGEST</h3>

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

<h2>Endpoints</h2>

<h3>Public endpoints</h3>

<h4><code>GET /ping</code></h4>
<p>Returns <code>pong</code></p>

<h4>Accounts</h4>

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

<h3>User authenticated endpoints</h3>
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

<h4>Devices</h4>

<h4><code>GET /accounts/me/devices</code></h4>
<p>Return the user registered devices.</p>

<h4><code>DELETE /accounts/me/devices/{uuid}</code></h4>
<p>Remove one of the user registered devices.</p>

<h3>Admin endpoints</h3>

<p>Those endpoints are authenticated and requires an admin account.</p>

<h4><code>POST /accounts</code></h4>
<p>To create an account directly from the API.</p>
<p>If <code>activated</code> is set to <code>false</code> a random generated <code>confirmation_key</code> will be returned to allow further activation using the public endpoints.</p>

<p>JSON parameters:</p>
<ul>
    <li><code>username</code> unique username, minimum 6 characters</li>
    <li><code>password</code> required minimum 6 characters</li>
    <li><code>algorithm</code> required, values can be <code>SHA-256</code> or <code>MD5</code></li>
    <li><code>domain</code> optional, the value is set to the default registration domain if not set</li>
    <li><code>activated</code> optional, a boolean, set to <code>false</code> by default</li>
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

@endsection
