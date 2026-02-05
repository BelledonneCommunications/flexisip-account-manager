<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CallForwarding extends Model
{
    protected $casts = [
        'enabled' => 'boolean'
    ];
    protected $fillable = ['enabled', 'account_id', 'type', 'forward_to', 'sip_uri'];
    protected $hidden = ['account_id'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
