<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'color_id',
        'size_id',
        'region_id',
        'sim_id',
        'storage_id',
        'warrenty_id',
        'device_condition_id',
        'unit_id',
        'qty',
        'unit_price',
        'avg_cost_price',
        'total_price',
        'special_discount',
        'reward_points',
        'warehouse_id',
        'warehouse_room_id',
        'warehouse_room_cartoon_id',
        'store_id'
    ];

    protected $casts = [
        'avg_cost_price' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'special_discount' => 'decimal:2',
        'qty' => 'decimal:2'
    ];

    public function order() {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product() {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
