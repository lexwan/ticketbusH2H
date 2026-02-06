<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionFee extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'transaction_id',
        'mitra_id',
        'fee_type',
        'fee_value',
        'fee_amount'
    ];

    protected $casts = [
        'fee_value' => 'decimal:2',
        'fee_amount' => 'decimal:2'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'mitra_id');
    }
}
