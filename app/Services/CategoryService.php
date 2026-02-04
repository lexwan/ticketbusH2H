<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CategoryService
{
    /**
     * Get all active categories
     */
    public function getAllCategories(): Collection
    {
        return Category::where('is_active', true)->get();
    }

    /**
     * Create new category with auto-generated slug
     */
    public function createCategory(array $data): Category
    {
        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $data['is_active'] ?? true;

        return Category::create($data);
    }

    /**
     * Update existing category
     */
    public function updateCategory(Category $category, array $data): Category
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return $category;
    }

    /**
     * Soft delete category (set is_active = false)
     */
    public function deleteCategory(Category $category): bool
    {
        return $category->update([
            'is_active' => false,
        ]);
    }

    /**
     * Get category with its products
     */
    public function getCategoryWithProducts(Category $category): Category
    {
        return $category->load('products');
    }
}
