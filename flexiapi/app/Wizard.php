<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\WizardFactory;

class Wizard extends Model
{
    use HasFactory;

    protected $primaryKey = 'token';
    protected $keyType = 'string';
    public $incrementing = false;

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

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function provisionedAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'provisioning_account_id');
    }

    public function getUrlAttribute()
    {
        return route('wizard.show', $this->attributes['token']);
    }
}
