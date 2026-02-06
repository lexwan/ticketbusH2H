<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerFeeLedger extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'mitra_id',
        'transaction_id',
        'amount',
        'type',
        'description'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'mitra_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
