<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\UploadProductImagesRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ProductService;
use App\Services\ProductImageService;
use App\Services\SearchService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

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
        protected SearchService $searchService,
        protected ProductImageService $productImageService
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

    /**
     * Upload images for product.
     *
     * @param UploadProductImagesRequest $request
     * @param Product $product
     * @return JsonResponse
     */
    public function uploadImages(UploadProductImagesRequest $request, Product $product): JsonResponse
    {
        $images = $this->productImageService->uploadImages(
            $product,
            $request->file('images')
        );

        return $this->createdResponse(
            ['images' => $images],
            'Images uploaded successfully'
        );
    }

    /**
     * Delete product image.
     *
     * @param Product $product
     * @param ProductImage $image
     * @return JsonResponse
     */
    public function deleteImage(Product $product, ProductImage $image): JsonResponse
    {
        // Ensure image belongs to product
        if ($image->product_id !== $product->id) {
            return $this->errorResponse('Image not found for this product', 404);
        }

        $this->productImageService->deleteImage($image);

        return $this->deletedResponse('Image deleted successfully');
    }

    /**
     * Set image as primary.
     *
     * @param Product $product
     * @param ProductImage $image
     * @return JsonResponse
     */
    public function setPrimaryImage(Product $product, ProductImage $image): JsonResponse
    {
        // Ensure image belongs to product
        if ($image->product_id !== $product->id) {
            return $this->errorResponse('Image not found for this product', 404);
        }

        $updatedImage = $this->productImageService->setPrimaryImage($image);

        return $this->successResponse(
            ['image' => $updatedImage],
            'Primary image set successfully'
        );
    }
}
