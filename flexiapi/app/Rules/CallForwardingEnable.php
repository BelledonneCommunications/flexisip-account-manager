<?php

namespace App\Rules;

use App\Account;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Request;

class CallForwardingEnable implements ValidationRule
{
    public function __construct(private Request $request, private Account $account)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value == true) {
            $filter = $this->request->get('type') == 'always' ? ['away', 'busy'] : ['always'];

            if ($this->account->callForwardings()->whereIn('type', $filter)->where('enabled', true)->exists()) {
                $fail('type: always and type: always/busy cannot be enabled at the same time');
            }
        }
    }
}
