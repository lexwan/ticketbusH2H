<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionPassenger extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'transaction_id',
        'name',
        'identity_number',
        'seat_number'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
