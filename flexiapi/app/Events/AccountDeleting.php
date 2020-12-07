<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use App\Account;

class AccountDeleting
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(Account $account)
    {
        $account->alias()->delete();
        $account->passwords()->delete();
        $account->nonces()->delete();
        $account->admin()->delete();
        $account->apiKey()->delete();
        $account->emailChanged()->delete();
    }
}
