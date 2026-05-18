<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Wizard extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $primaryKey = 'token';
    protected $keyType = 'string';
    protected $appends = ['url'];
    protected $hidden = ['account_id', 'created_at', 'updated_at'];

    public const LINPHONE_ACTION = ['call', 'show', 'bye', 'accept', 'decline'];

    protected $fillable = [
        'account_id',
        'provisioning_account_id',
        'sip',
        'linphone_action',
        'linphone_use_sips',
        'used_at',
    ];

    protected static function booted(): void
    {
        self::creating(static function (Wizard $wizard): void {
            $wizard->token = Str::random(8);
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function provisionedAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'provisioning_account_id');
    }

    public function getUrlAttribute(): string
    {
        return route('wizard.show', $this->attributes['token']);
    }
}
