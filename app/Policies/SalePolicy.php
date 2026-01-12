<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SalePolicy
{
    public function view(User $user, Sale $sale)
    {
        return $sale->user_id === $user->id;
    }

    public function update(User $user, Sale $sale)
    {
        return $sale->user_id === $user->id;
    }

    public function delete(User $user, Sale $sale)
    {
        return $sale->user_id === $user->id;
    }
}
