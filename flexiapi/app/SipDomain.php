<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SipDomain extends Model
{
    use HasFactory;

    protected $hidden = ['id'];
    protected $casts = [
        'super' => 'boolean',
        'disable_chat_feature' => 'boolean',
        'disable_meetings_feature' => 'boolean',
        'disable_broadcast_feature' => 'boolean',
        'hide_settings' => 'boolean',
        'hide_account_settings' => 'boolean',
        'disable_call_recordings_feature' => 'boolean',
        'only_display_sip_uri_username' => 'boolean',
        'assistant_hide_create_account' => 'boolean',
        'assistant_disable_qr_code' => 'boolean',
        'assistant_hide_third_party_account' => 'boolean',
    ];

    public function accounts()
    {
        return $this->hasMany(Account::class, 'domain', 'domain');
    }
}
