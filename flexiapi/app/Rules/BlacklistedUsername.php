<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class BlacklistedUsername implements Rule
{
    public function passes($attribute, $value)
    {
        if (!empty(config('app.blacklisted_usernames'))) {
            foreach (explode(',', config('app.blacklisted_usernames')) as $username) {
                if ($value == $username) return false;

                // Regex rules
                $regex = '/' . $username . '/';

                if (isRegularExpression($regex)) {
                    $matches = [];
                    preg_match($regex, $value, $matches);
                    if (count($matches) > 0) return false;
                }
            }
        }

        return true;
    }

    public function message()
    {
        return 'Username already used.';
    }
}
