<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountAction extends Model
{
    use HasFactory;

    public static $protocols = ['sipinfo' => 'SIPInfo', 'rfc2833' => 'RFC2833'];

    public static function protocolsRule()
    {
        return implode(',', array_keys(self::$protocols));
    }

    public function getResolvedProtocolAttribute()
    {
        return self::$protocols[$this->attributes['protocol']];
    }

    public function account()
    {
        return $this->belongsTo('App\Account');
    }
}
