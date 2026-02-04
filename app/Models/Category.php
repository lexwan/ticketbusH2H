<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    /**
     * Route Model Binding pakai slug
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Relasi: Category memiliki banyak Product
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
