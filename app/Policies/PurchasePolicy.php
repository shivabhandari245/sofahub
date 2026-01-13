<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PurchaseModel;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchasePolicy
{
    use HandlesAuthorization;

    // Only the owner can view a purchase
    public function view(User $user, PurchaseModel $purchase)
    {
        return $user->id === $purchase->user_id;
    }

    // Only the owner can update
    public function update(User $user, PurchaseModel $purchase)
    {
        return $user->id === $purchase->user_id;
    }

    // Only the owner can delete
    public function delete(User $user, PurchaseModel $purchase)
    {
        return $user->id === $purchase->user_id;
    }

    // Only the owner can edit
    public function edit(User $user, PurchaseModel $purchase)
    {
        return $user->id === $purchase->user_id;
    }
}
