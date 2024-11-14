<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cart_id',
        'status',
        'total_amount',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public static function createOrder($userId, $cartId, $totalAmount)
    {
        return self::create([
            'user_id' => $userId,
            'cart_id' => $cartId,
            'status' => 'pending',
            'total_amount' => $totalAmount,
        ]);
    }

    public static function getOrders($filters)
    {
        $query = self::query();
        if (!empty($filters['id'])) {
            $query->where('id', $filters['id']);
        }
        if (!empty($filters['cart_id'])) {
            $query->where('cart_id', $filters['cart_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['minPrice'])) {
            $query->where('total_amount', '>=', $filters['minPrice']);
        }
        if (isset($filters['maxPrice'])) {
            $query->where('total_amount', '<=', $filters['maxPrice']);
        }
        if (!empty($filters['sort']) && in_array($filters['sort'], ['total_amount', 'created_at', 'updated_at'])) {
            $query->orderBy($filters['sort'], $filters['order'] ?? 'asc');
        }
        $page = $filters['page'] ?? 1;
        $size = $filters['size'] ?? 100;
        $orders = $query->paginate($size, ['*'], 'page', $page);
        return [
            'status' => 'success',
            'message' => '',
            'current_page' => $orders->currentPage(),
            'per_page' => $orders->perPage(),
            'total' => $orders->total(),
            'last_page' => $orders->lastPage(),
            'next_page' => ($orders->currentPage() == $orders->lastPage()) ? false : true,
            'data' => $orders->items()
        ];
    }
}
