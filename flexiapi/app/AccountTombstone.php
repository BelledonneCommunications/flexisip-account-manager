<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountTombstone extends Model
{
    protected $table = 'accounts_tombstones';

    use HasFactory;
}
