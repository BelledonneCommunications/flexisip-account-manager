<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VcardStorage extends Model
{
    use HasFactory;

    protected $table = 'vcards_storage';
    protected $hidden = ['id', 'account_id', 'uuid'];


    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
