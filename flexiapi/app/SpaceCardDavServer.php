<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SpaceCardDavServer extends Model
{
    protected $hidden = ['space_id'];
    protected $table = 'space_carddav_servers';
    protected $fillable = ['uri', 'enabled', 'min_characters', 'results_limist', 'use_exact_match_policy', 'timeout', 'delay', 'fields_for_user_input', 'fields_for_domain'];

    protected $casts = [
        'enabled' => 'boolean',
        'use_exact_match_policy' => 'boolean',
    ];

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'account_carddav_credentials', 'space_carddav_server_id', 'account_id');
    }

    public function getNameAttribute()
    {
        return __('CardDav Server') . ' ' . $this->id;
    }

    public function getProvisioningSection($config, int $remoteContactDirectoryCounter)
    {
        $dom = $config->ownerDocument;

        $section = $dom->createElement('section');
        $section->setAttribute('name', 'remote_contact_directory_' . $remoteContactDirectoryCounter);

        $entry = $dom->createElement('entry', $this->enabled ? '1': '0');
        $entry->setAttribute('name', 'enabled');
        $section->appendChild($entry);

        $entry = $dom->createElement('entry', 'carddav');
        $entry->setAttribute('name', 'type');
        $section->appendChild($entry);

        $entry = $dom->createElement('entry', $this->uri);
        $entry->setAttribute('name', 'uri');
        $section->appendChild($entry);

        $entry = $dom->createElement('entry', $this->min_characters);
        $entry->setAttribute('name', 'min_characters');
        $section->appendChild($entry);

        $entry = $dom->createElement('entry', $this->results_limit);
        $entry->setAttribute('name', 'results_limit');
        $section->appendChild($entry);

        $entry = $dom->createElement('entry', $this->timeout);
        $entry->setAttribute('name', 'timeout');
        $section->appendChild($entry);

        $entry = $dom->createElement('entry', $this->delay);
        $entry->setAttribute('name', 'delay');
        $section->appendChild($entry);

        $entry = $dom->createElement('entry', $this->fields_for_user_input);
        $entry->setAttribute('name', 'carddav_fields_for_user_input');
        $section->appendChild($entry);

        $entry = $dom->createElement('entry', $this->fields_for_domain);
        $entry->setAttribute('name', 'carddav_fields_for_domain');
        $section->appendChild($entry);

        $entry = $dom->createElement('entry', $this->use_exact_match_policy ? '1': '0');
        $entry->setAttribute('name', 'carddav_use_exact_match_policy');
        $section->appendChild($entry);

        $config->appendChild($section);
    }
}
