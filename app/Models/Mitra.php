<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mitra extends Model
{
    protected $table = 'mitra';
    
    protected $fillable = [
        'code',
        'name',
        'email',
        'phone',
        'status',
        'balance'
    ];

    protected $casts = [
        'balance' => 'decimal:2'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'mitra_id');
    }

    public function topups()
    {
        return $this->hasMany(Topup::class, 'mitra_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'mitra_id');
    }

    public function feeLedgers()
    {
        return $this->hasMany(PartnerFeeLedger::class, 'mitra_id');
    }
}
