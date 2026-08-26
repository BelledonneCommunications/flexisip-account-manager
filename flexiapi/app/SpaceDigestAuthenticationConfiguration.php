<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpaceDigestAuthenticationConfiguration extends Model
{
    use HasFactory;

    protected $fillable = ['realm', 'default_password_algorithm', 'space_id'];
    protected $hidden = ['space_id', 'id', 'created_at', 'updated_at'];
    protected $casts = [
        'default_password_algorithm' => PasswordAlgorithm::class,
    ];

    public function space()
    {
        return $this->belongsTo(Space::class);
    }
}
