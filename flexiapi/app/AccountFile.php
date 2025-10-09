<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AccountFile extends Model
{
    use HasUuids;

    public const VOICEMAIL_CONTENTTYPES = ['audio/opus', 'audio/wav'];
    protected $hidden = ['account_id', 'updated_at'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function getMaxUploadSizeAttribute(): ?int
    {
        return maxUploadSize();
    }

    public function getUploadUrlAttribute(): ?string
    {
        return route('file.upload', $this->attributes['id']);
    }
}
