<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Space extends Model
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
        'expire_at' => 'date',
    ];

    public const HOST_REGEX = '[\w\-]+';
    public const DOMAIN_REGEX = '[\w\-\.]+';

    public function accounts()
    {
        return $this->hasMany(Account::class, 'domain', 'domain');
    }

    public function admins()
    {
        return $this->accounts()->where('admin', true);
    }

    public function scopeNotFull(Builder $query)
    {
        return $query->where('max_accounts', 0)
                     ->orWhereRaw('max_accounts > (select count(*) from accounts where domain = spaces.domain)');
    }

    public function getAccountsPercentageAttribute(): int
    {
        if ($this->max_accounts != null) {
            return (int)($this->accounts()->count() / $this->max_accounts * 100);
        }

        return 0;
    }

    public function isFull(): bool
    {
        return $this->max_accounts > 0 && ($this->accounts()->count() >= $this->max_accounts);
    }

    public function isExpired(): bool
    {
        return $this->expire_at && $this->expire_at->isPast();
    }

    public function isRoot(): bool
    {
        return $this->host == config('app.root_host');
    }

    public function getAccountsPercentageClassAttribute(): string
    {
        if ($this->getAccountsPercentageAttribute() >= 80) {
            return 'orange';
        }

        if ($this->getAccountsPercentageAttribute() >= 60) {
            return 'yellow';
        }

        return 'green';
    }

    public function getDaysLeftAttribute(): ?int
    {
        if ($this->expire_at != null) {
            return (int)$this->expire_at->diffInDays(Carbon::now());
        }

        return null;
    }
}
