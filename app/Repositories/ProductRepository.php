<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository
{
    public function __construct(
        private readonly Product $product
    ) {
    }

    public function getAll(): LengthAwarePaginator
    {
        return $this->product->paginate();
    }

    public function getSearch($request): LengthAwarePaginator
    {
        return $this->product->search($request['search'])->paginate();
    }

    public function update($product, $validatedData): bool
    {
        return $product->update($validatedData);
    }
}