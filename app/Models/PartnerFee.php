<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerFee extends Model
{
    protected $fillable = [
        'mitra_id',
        'type',
        'value',
        'active'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'active' => 'boolean'
    ];

    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'mitra_id');
    }
}
