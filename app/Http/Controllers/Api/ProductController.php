<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use App\Services\SearchService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    use ApiResponse;
    /**
     * ProductController constructor.
     *
     * @param ProductService $productService
     */
    public function __construct(
        protected ProductService $productService,
        protected SearchService $searchService
    ) {}

    /**
     * Display a listing of products.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // Check if there are search/filter parameters
        $hasFilters = request()->hasAny(['q', 'min_price', 'max_price', 'stock_status', 'sort_by']);
        
        if ($hasFilters) {
            // Use search service if filters are present
            $filters = request()->only([
                'q', 'min_price', 'max_price', 'stock_status', 'sort_by', 'sort_order', 'per_page'
            ]);
            
            $products = $this->searchService->searchProducts($filters);
            
            return $this->paginatedResponse(
                $products->through(fn($product) => new ProductResource($product)),
                'Products retrieved successfully',
                ['filters_applied' => array_filter($filters)]
            );
        }
        
        // Default product listing
        $products = $this->productService->getProducts(
            perPage: request()->get('per_page', 15)
        );

        return $this->paginatedResponse(
            $products->through(fn($product) => new ProductResource($product)),
            'Products retrieved successfully'
        );
    }

    /**
     * Store a newly created product.
     *
     * @param StoreProductRequest $request
     * @return JsonResponse
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $productData = $request->validated();
        
        // Handle image uploads if present
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $productData['images'] = $images;
        }
        
        $product = $this->productService->createProduct($productData);

        return $this->createdResponse(
            new ProductResource($product),
            'Product created successfully'
        );
    }

    /**
     * Display the specified product.
     *
     * @param Product $product
     * @return JsonResponse
     */
    public function show(Product $product): JsonResponse
    {
        return $this->successResponse(
            new ProductResource($product),
            'Product retrieved successfully'
        );
    }

    /**
     * Update the specified product.
     *
     * @param UpdateProductRequest $request
     * @param Product $product
     * @return JsonResponse
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $productData = $request->validated();
        
        // Handle image uploads if present
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $productData['images'] = $images;
        }
        
        $updatedProduct = $this->productService->updateProduct(
            $product,
            $productData
        );

        return $this->successResponse(
            new ProductResource($updatedProduct),
            'Product updated successfully'
        );
    }

    /**
     * Remove the specified product.
     *
     * @param Product $product
     * @return JsonResponse
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->productService->deleteProduct($product);

        return $this->deletedResponse('Product deleted successfully');
    }
}
