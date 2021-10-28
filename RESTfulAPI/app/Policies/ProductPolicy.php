<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Traits\AdminActions;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization, AdminActions;

    public function addCategory(User $user, Product $product)
    {
        return $user->id === $product->seller->id;
    }

    public function deleteCategory(User $user, Product $product)
    {
        return $user->id === $product->seller->id;
    }
}
