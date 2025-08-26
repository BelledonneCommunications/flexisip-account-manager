## Spaces CardDav Servers

### `GET /spaces/{domain}/carddavs`
<span class="badge badge-error">Super Admin</span>

List current CardDavs servers.

### `GET /spaces/{domain}/carddavs/{id}`
<span class="badge badge-error">Super Admin</span>

Get a specific CardDav server.

### `POST /spaces/{domain}/carddavs`
<span class="badge badge-error">Super Admin</span>

Create a CardDav server configuration.

JSON parameters:

* `uri` **required**, HTTP address of the server
* `port` **required**, integer, the port
* `enabled`, boolean
* `use_exact_match_policy`, boolean, whether match must be exact or approximate
* `min_characters`, integer, min characters to search
* `results_limit`, integer, limit the number of results, 0 to infinite
* `timeout`, integer, request timeout in seconds
* `delay`, integer, delay in milliseconds before submiting the request
* `fields_for_user_input`, comma separated list of vcard fields to match with user input
* `fields_for_domain`, comma separated list of vcard fields to match for SIP domain

### `PUT /spaces/{domain}/carddavs/{id}`
<span class="badge badge-error">Super Admin</span>

Update a CardDav server configuration.

JSON parameters:

* `uri` **required**, HTTP address of the server
* `port` **required**, integer, the port
* `enabled`, boolean
* `use_exact_match_policy`, boolean, whether match must be exact or approximate
* `min_characters`, integer, min characters to search
* `results_limit`, integer, limit the number of results, 0 to infinite
* `timeout`, integer, request timeout in seconds
* `delay`, integer, delay in milliseconds before submiting the request
* `fields_for_user_input`, comma separated list of vcard fields to match with user input
* `fields_for_domain`, comma separated list of vcard fields to match for SIP domain

### `DELETE /spaces/{domain}/carddavs/{id}`
<span class="badge badge-error">Super Admin</span>

Delete a specific CardDav server configuration.