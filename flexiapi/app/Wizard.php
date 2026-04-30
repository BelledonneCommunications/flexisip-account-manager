<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wizard extends Model
{
    protected $primaryKey = 'token';
    protected $keyType = ' string';
    public $incrementing = false;

    protected $fillable = [
        'account_id',
        'provisioning_account_id',
        'sip',
        'linphone_action',
        'linphone_use_sips',
        'used_at',
    ];

    protected static function booted()
    {
        static::creating(function($wizard) {
            if (empty($wizard->token)) {
                do {
                    $token = Str::random(8);
                } while (static::where('token', $token)->exists());

                $wizard->token = $token;
            }
        });
    }

    public function account(): belongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }


    public function provisionedAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'provisioning_account_id');
    }
}
