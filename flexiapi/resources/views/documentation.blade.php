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
<p>Restricted endpoints are protected using a DIGEST authentication mechanism.</p>

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

<p>Current implemented endpoints</p>

<h4><code>GET /devices</code></h4>

<p>Return the user registered devices.</p>

<h4><code>DELETE /devices/{uuid}</code></h4>

<p>Remove one of the user registered devices.</p>
@endsection
