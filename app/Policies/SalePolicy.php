<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SalePolicy
{
    /**
     * Determine whether the user can view any sales (listing).
     */
    public function viewAny(User $user)
    {
        // Any authenticated user can view their own sales list
        return Response::allow();
    }

    /**
     * Determine whether the user can view a specific sale.
     */
    public function view(User $user, Sale $sale)
    {
        return $sale->user_id === $user->id
            ? Response::allow()
            : Response::deny('You do not own this sale.');
    }

    /**
     * Determine whether the user can create sales.
     */
    public function create(User $user)
    {
        // Any authenticated user can create a sale
        return Response::allow();
    }

    /**
     * Determine whether the user can update a sale.
     */
    public function update(User $user, Sale $sale)
    {
        return $sale->user_id === $user->id
            ? Response::allow()
            : Response::deny('You cannot edit this sale.');
    }

    /**
     * Determine whether the user can delete a sale.
     */
    public function delete(User $user, Sale $sale)
    {
        return $sale->user_id === $user->id
            ? Response::allow()
            : Response::deny('You cannot delete this sale.');
    }

    /**
     * Optional: Add a return policy if you have a "return" action.
     */
    public function return(User $user, Sale $sale)
    {
        return $sale->user_id === $user->id
            ? Response::allow()
            : Response::deny('You cannot return this sale.');
    }
}
