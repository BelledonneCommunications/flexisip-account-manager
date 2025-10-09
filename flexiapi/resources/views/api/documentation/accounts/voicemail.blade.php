## Voicemails

### `GET /accounts/{id/me}/voicemails`
<span class="badge badge-warning">Admin</span>
<span class="badge badge-info">User</span>

Return the currently stored voicemails

### `GET /accounts/{id/me}/voicemails/{uuid}`
<span class="badge badge-warning">Admin</span>
<span class="badge badge-info">User</span>

```
{
    id: '{uuid}',
    sip_from: '{sip_address}',
    file_url: 'https://{the file_url}',
    file_size: 2451400, // the file size, in bytes
    content_type: 'audio/{format}',
    created_at: '2025-10-09T12:59:32Z',
    uploaded_at: '2025-10-09T12:59:40Z'
}
```

Return a stored voicemail

### `POST /accounts/{id/me}/vcards-storage`
<span class="badge badge-warning">Admin</span>
<span class="badge badge-info">User</span>

Create a new voicemail slot

JSON parameters:

* `sip_from`, mandatory, a valid SIP address
* `content_type`, mandatory, the content type of the audio file to upload, must be `audio/opus` or `audio/wav`

This endpoint will return the following JSON:

```
{
    id: '{uuid}',
    sip_from: '{sip_address}',
    put_url: 'https://{upload_service_unique_url}', // unique URL generated to upload the audio file
    max_file_size: 3000000, // here 3MB file size limit, in bytes
    content_type: 'audio/{format}',
    created_at: '2025-10-09T12:59:32Z', // time of the slot creation
    uploaded_at: null // time when the slot was filled with the audio file
}
```

### `DELETE /accounts/{id/me}/voicemails/{uuid}`
<span class="badge badge-warning">Admin</span>
<span class="badge badge-info">User</span>

Delete a stored voicemail, if the file is managed by the platform it will be deleted as well