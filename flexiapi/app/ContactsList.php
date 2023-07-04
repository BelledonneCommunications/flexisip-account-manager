<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactsList extends Model
{
    use HasFactory;

    protected $withCount = ['contacts'];

    public function contacts()
    {
        return $this->belongsToMany(Account::class, 'contacts_list_contact', 'contacts_list_id', 'contact_id');
    }
}
