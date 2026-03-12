<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CallForwarding extends Model
{
    protected $casts = [
        'enabled' => 'boolean'
    ];
    protected $fillable = ['enabled', 'account_id', 'type', 'forward_to', 'sip_uri'];
    protected $hidden = ['account_id', 'contact'];
    protected $with = ['contact'];
    protected $appends = ['contact_sip_uri'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function contact()
    {
        return $this->hasOne(Account::class, 'id', 'contact_id');
    }

    public function getContactSipUriAttribute(): ?string
    {
        if ($this->contact) return $this->contact->sip_uri;
        return null;
    }
}
