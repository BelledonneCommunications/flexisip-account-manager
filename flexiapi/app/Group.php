<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    protected $primaryKey = 'id';
    protected $hidden = ['space_id'];
    protected $fillable = ['username', 'strategy', 'call_forwarding_id'];
    protected $withCount = ['accounts'];

    public const STRATEGIES = [
        'ring_all' => 'Ring_all',
        'sequential' => 'Sequential',
        'round_robin' => 'Round_robin'
    ];

    public const PAGINATION = 20;

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class);
    }

}
