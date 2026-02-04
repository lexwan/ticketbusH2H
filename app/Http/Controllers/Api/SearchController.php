<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchProductsRequest;
use App\Http\Resources\ProductResource;
use App\Services\SearchService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    use ApiResponse;

    public function __construct(
        private SearchService $searchService
    ) {}

    /**
     * Search products with filters.
     */
    public function searchProducts(SearchProductsRequest $request): JsonResponse
    {
        $filters = $request->validated();

        $products = $this->searchService->searchProducts($filters);

        return $this->paginatedResponse(
            $products->through(fn($product) => new ProductResource($product)),
            'Products found successfully',
            [
                'filters_applied' => array_filter($filters),
                'total_results' => $products->total()
            ]
        );
    }

    /**
     * Get search suggestions.
     */
    public function suggestions(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100']
        ]);

        $suggestions = $this->searchService->getSearchSuggestions(
            $request->get('q'),
            $request->get('limit', 10)
        );

        return $this->successResponse(
            ['suggestions' => $suggestions],
            'Suggestions retrieved successfully'
        );
    }

    /**
     * Get filter options for frontend.
     */
    public function filterOptions(): JsonResponse
    {
        $options = $this->searchService->getFilterOptions();

        return $this->successResponse(
            $options,
            'Filter options retrieved successfully'
        );
    }

    /**
     * Get popular search terms.
     */
    public function popularTerms(): JsonResponse
    {
        $terms = $this->searchService->getPopularSearchTerms(
            request()->get('limit', 10)
        );

        return $this->successResponse(
            ['popular_terms' => $terms],
            'Popular search terms retrieved successfully'
        );
    }
}
