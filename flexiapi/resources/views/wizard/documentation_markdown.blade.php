# Wizard

The Wizard page handles custom SIP URIs, provides the related actions when opening Linphone, and proposes to set it up if required.

## Using a token

The Wizard can rely on `tokens` generated using the API; check out the <a href="{{ route('api') }}#post-wizard">related documentation</a>.

When providing a `token` generated using the API to the Wizard URL (`/wizard/{token}`), the `token` settings will be applied depending on how it was configured:

If the `token` is provisioning an account, triggering a specific action, or trying to reach a specific SIP URI, the `Open the app` button will point to a custom `sip-linphone` schema URI that your Linphone app will be able to handle.

## Using `GET` parameters

You can also directly pass some of the configuration parameters in the page URL as `GET` parameters.

* `sip`: Pass a SIP address to reach
* `linphone-action`: Trigger a Linphone action when opening the app. Accepted values: `call`, `show`, `bye`, `accept`, `decline`.
* `linphone-use-sips`: Enforce the use of SIPS

For example:

```
> GET /wizard?sip=sip%3Ajohn%40sip.linphone.org&linphone-action=call
```

If a token is provided, it will overwrite all the `GET` parameters.