<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;

class AccountFile extends Model
{
    use HasUuids;

    public const VOICEMAIL_CONTENTTYPES = ['audio/opus', 'audio/wav'];
    public const FILES_PATH = 'files';
    protected $hidden = ['account_id', 'updated_at', 'sending_by_mail_at', 'sent_by_mail_at', 'sending_by_mail_tryouts'];
    protected $appends = ['download_url'];
    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::deleting(function ($category) {
            Storage::delete($this->getPathAttribute());
        });
    }

    public function account()
    {
        return $this->belongsTo(Account::class)->withoutGlobalScopes();
    }

    public function getMaxUploadSizeAttribute(): ?int
    {
        return maxUploadSize();
    }

    public function getUploadUrlAttribute(): ?string
    {
        return route('file.upload', $this->attributes['id']);
    }

    public function getPathAttribute(): string
    {
        return self::FILES_PATH . '/' . $this->attributes['name'];
    }

    public function getDownloadUrlAttribute(): ?string
    {
        return !empty($this->attributes['name'])
            && !empty($this->attributes['id'])
            ? route('file.show', [$this->attributes['id'], $this->attributes['name']])
            : null;
    }
}
