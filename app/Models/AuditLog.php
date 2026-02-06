<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'action',
        'payload',
        'ip_address'
    ];

    protected $casts = [
        'payload' => 'json'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
