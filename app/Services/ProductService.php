<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    /**
     * Get paginated list of products.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getProducts(int $perPage = 15): LengthAwarePaginator
    {
        return Product::with('category')->latest()->paginate($perPage);
    }

    /**
     * Get all products without pagination.
     *
     * @return Collection
     */
    public function getAllProducts(): Collection
    {
        return Product::with('category')->get();
    }

    /**
     * Find a product by ID.
     *
     * @param int $id
     * @return Product|null
     */
    public function findProduct(int $id): ?Product
    {
        return Product::with('category')->find($id);
    }

    /**
     * Create a new product.
     *
     * @param array $data
     * @return Product
     */
    public function createProduct(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Update an existing product.
     *
     * @param Product $product
     * @param array $data
     * @return Product
     */
    public function updateProduct(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    /**
     * Delete a product.
     *
     * @param Product $product
     * @return bool
     */
    public function deleteProduct(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Check if SKU is available (not used by another product).
     *
     * @param string $sku
     * @param int|null $excludeId
     * @return bool
     */
    public function isSkuAvailable(string $sku, ?int $excludeId = null): bool
    {
        $query = Product::where('sku', $sku);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    /**
     * Update stock for a product.
     *
     * @param Product $product
     * @param int $quantity
     * @param string $operation (add|subtract)
     * @return Product
     */
    public function updateStock(Product $product, int $quantity, string $operation = 'add'): Product
    {
        if ($operation === 'subtract') {
            $newStock = max(0, $product->stock - $quantity);
        } else {
            $newStock = $product->stock + $quantity;
        }
        
        $product->update(['stock' => $newStock]);
        return $product->fresh();
    }
}
