<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopupHistory extends Model
{
    protected $fillable = [
        'topup_id',
        'mitra_id',
        'amount',
        'balance_before',
        'balance_after',
        'description',
    ];

    public $timestamps = false;

    public function topup()
    {
        return $this->belongsTo(Topup::class);
    }

    public function mitra()
    {
        return $this->belongsTo(Mitra::class);
    }
}
