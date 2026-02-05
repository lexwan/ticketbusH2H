<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchService
{
    /**
     * Search products with filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function searchProducts(array $filters): LengthAwarePaginator
    {
        $query = Product::query();

        // Search by keyword (name, sku)
        if (!empty($filters['q'])) {
            $query->where(function (Builder $q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['q'] . '%')
                  ->orWhere('sku', 'like', '%' . $filters['q'] . '%');
            });
        }

        // Filter by price range
        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Filter by stock status
        if (!empty($filters['stock_status'])) {
            switch ($filters['stock_status']) {
                case 'in_stock':
                    $query->where('stock', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('stock', '=', 0);
                    break;
                case 'low_stock':
                    $query->where('stock', '>', 0)->where('stock', '<=', 10);
                    break;
            }
        }

        // Sort options
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        $allowedSorts = ['name', 'price', 'stock', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = min($filters['per_page'] ?? 15, 50); // Max 50 per page
        
        $results = $query->paginate($perPage);
        
        // Log search if there's a query
        if (!empty($filters['q'])) {
            $this->logSearch($filters['q'], $results->total());
        }
        
        return $results;
    }

    /**
     * Get search suggestions.
     *
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function getSearchSuggestions(string $query, int $limit = 10): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        return Product::where('name', 'like', '%' . $query . '%')
            ->orWhere('sku', 'like', '%' . $query . '%')
            ->limit($limit)
            ->pluck('name')
            ->toArray();
    }

    /**
     * Get filter options for frontend.
     *
     * @return array
     */
    public function getFilterOptions(): array
    {
        return [
            'price_range' => [
                'min' => Product::min('price') ?? 0,
                'max' => Product::max('price') ?? 0
            ],
            'stock_status_options' => [
                'in_stock' => 'In Stock',
                'out_of_stock' => 'Out of Stock',
                'low_stock' => 'Low Stock (≤10)'
            ],
            'sort_options' => [
                'name_asc' => 'Name (A-Z)',
                'name_desc' => 'Name (Z-A)',
                'price_asc' => 'Price (Low to High)',
                'price_desc' => 'Price (High to Low)',
                'stock_asc' => 'Stock (Low to High)',
                'stock_desc' => 'Stock (High to Low)',
                'created_at_desc' => 'Newest First',
                'created_at_asc' => 'Oldest First'
            ]
        ];
    }

    /**
     * Get popular search terms.
     *
     * @param int $limit
     * @return array
     */
    public function getPopularSearchTerms(int $limit = 10): array
    {
        return \App\Models\SearchLog::selectRaw('query, COUNT(*) as search_count')
            ->where('created_at', '>=', now()->subDays(30)) // Last 30 days
            ->groupBy('query')
            ->orderByDesc('search_count')
            ->limit($limit)
            ->pluck('query')
            ->toArray();
    }

    /**
     * Log search query.
     *
     * @param string $query
     * @param int $resultsCount
     * @return void
     */
    public function logSearch(string $query, int $resultsCount): void
    {
        \App\Models\SearchLog::create([
            'query' => $query,
            'user_id' => auth()->id(),
            'results_count' => $resultsCount,
            'ip_address' => request()->ip(),
        ]);
    }
}