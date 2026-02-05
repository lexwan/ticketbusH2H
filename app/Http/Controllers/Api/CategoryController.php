<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ApiResponse;

    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
        
        // Authentication required for create, update, delete
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
        // Admin role required for create, update, delete
        $this->middleware('role:admin', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * List active categories.
     */
    public function index(): JsonResponse
    {
        $categories = $this->categoryService->getAllCategories();

        return $this->successResponse(
            CategoryResource::collection($categories),
            'Categories retrieved successfully'
        );
    }

    /**
     * Create new category (admin only).
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->createCategory($request->validated());

        return $this->createdResponse(
            new CategoryResource($category),
            'Category created successfully'
        );
    }

    /**
     * Show category with products.
     */
    public function show(Category $category): JsonResponse
    {
        $categoryWithProducts = $this->categoryService->getCategoryWithProducts($category);

        return $this->successResponse(
            new CategoryResource($categoryWithProducts),
            'Category retrieved successfully'
        );
    }

    /**
     * Update category (admin only).
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $updatedCategory = $this->categoryService->updateCategory($category, $request->validated());

        return $this->successResponse(
            new CategoryResource($updatedCategory),
            'Category updated successfully'
        );
    }

    /**
     * Delete category (admin only).
     */
    public function destroy(Category $category): JsonResponse
    {
        $this->categoryService->deleteCategory($category);

        return $this->deletedResponse('Category deleted successfully');
    }
}