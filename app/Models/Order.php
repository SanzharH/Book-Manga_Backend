<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const DELIVERY_METHODS = ['courier', 'point', 'pickup'];

    public const PAYMENT_METHODS = ['card', 'kaspi', 'cash'];

    protected $fillable = [
        'status',
        'shipping_name',
        'shipping_address',
        'phone',
        'delivery_method',
        'payment_method',
        'delivery_price',
    ];

    protected function casts(): array
    {
        return [
            'delivery_price' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
