<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;

        //admin 
        $this->middleware(['auth:sanctum', 'role:admin'])
        ->only(['store', 'update', 'destroy']);
    }

    /**
     * List aktif kategory
    */
    public function index ()
    {
        $categories = $this->categoryService->getAllCategories();

        return response()->json([
            'status' => true,
            'message' => 'list categories',
            'data' => $categories,
        ]);
    }

    /**
     * Create baru kategori (admin)
     */
    public function store(Request $request)
    {
        $validated = $request->validated([
            'name' => 'required|string|max:225',
            'description' =>'nullable|string',
            'is_active' => 'boolean',
        ]);

        $category = $this->categoryService->createCategory($validated);

        return response()->json([
            'status' => true,
            'message' => 'Category created',
            'data' => $category,
        ], 201);
    }

    /**
     * show category with products
     */
    public function show(Category $category)
    {
        $category = $this->categoryService->getCategoryWithProducts($category);

        return response()->json([
            'status' => true,
            'message' => "category detail",
            'data' => $category,
        ]);
    }

    /**
     *  update category
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $category = $this->categoryServices->updateCategory($category, $validated);

        return response()->json([
            'status' => true,
            'message'=> 'Category Updated',
            'data' => $category,
        ]);
    }

    /**
     * Delete Category (admin)
     */
    public function destroy(Category $category)
    {
        $this->categoryService->deleteCategory($category);

        return response()->json([
            'status' => true,
            'message' => 'Category Deleted',
            'data' => null,
        ]);
    }
}
