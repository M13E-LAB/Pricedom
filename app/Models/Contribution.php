<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_name',
        'product_barcode',
        'price',
        'quantity',
        'category',
        'store_name',
        'location',
        'notes',
        'receipt_image',
        'contribution_type',
        'verified'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'verified' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
