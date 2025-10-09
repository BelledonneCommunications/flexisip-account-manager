<?php

namespace App\Rules;

use App\AccountFile;
use Illuminate\Contracts\Validation\Rule;

class AudioMime implements Rule
{
    public function __construct(private AccountFile $accountFile)
    {
    }

    public function passes($attribute, $file): bool
    {
        $mimeType = null;
        switch ($file->getMimeType()) {
            case 'audio/opus':
                $mimeType = 'audio/opus';
                break;

            case 'audio/vnd.wave':
            case 'audio/wav':
            case 'audio/wave':
            case 'audio/x-wav':
            case 'audio/x-pn-wav':
                $mimeType = 'audio/wav';
                break;
        }

        return $this->accountFile->content_type == $mimeType;
    }

    public function message()
    {
        return __('The file should have the declared mime-type');
    }
}
