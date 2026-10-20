<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CallForwarding extends Model
{
    protected $casts = [
        'enabled' => 'boolean'
    ];
    protected $fillable = ['enabled', 'account_id', 'type', 'contact_account_id', 'sip_account_id', 'voicemail_account_id'];
    protected $hidden = ['account_id', 'contactAccount', 'sipAccount', 'voicemailAccount'];
    protected $appends = ['sip_uri'];

    protected static function booted()
    {
        static::saving(function ($forwarding) {
            if (!$forwarding->voicemail_account_id
                && !$forwarding->sip_account_id
                && !$forwarding->contact_account_id) {
                throw new \RuntimeException('A call forwarding must have a target');
            }
        });
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function contactAccount()
    {
        return $this->hasOne(Account::class, 'id', 'contact_account_id');
    }

    public function sipAccount()
    {
        return $this->hasOne(Account::class, 'id', 'sip_account_id');
    }

    public function voicemailAccount()
    {
        return $this->hasOne(Account::class, 'id', 'voicemail_account_id');
    }

    public function getForwardToAttribute(): string
    {
        // Current form values

        if (old($this->type . '.forward_to') == 'voicemail') {
            return 'voicemail';
        }

        if (old($this->type . '.forward_to') == 'contact') {
            return 'contact';
        }

        if (old($this->type . '.forward_to') == 'sip_uri') {
            return 'sip_uri';
        }

        // Saved values

        if ($this->voicemail_account_id) {
            return 'voicemail';
        }

        if ($this->contact_account_id) {
            return 'contact';
        }

        return 'sip_uri';
    }

    public function getSipUriAttribute(): ?string
    {
        if ($this->voicemailAccount) {
            return $this->voicemailAccount->sip_uri;
        }

        if ($this->contactAccount) {
            return $this->contactAccount->sip_uri;
        }

        if ($this->sipAccount) {
            return $this->sipAccount->sip_uri;
        }

        return null;
    }
}
