<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
    ];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public static function getUserCart($userId)
    {
        $cart = Cart::where('user_id', $userId)->where('status', 'created')->first();
        if (!$cart) {
            $cart = Cart::create(['user_id' => $userId]);
        }
        return $cart;
    }

    public static function getCarts($filters)
    {
        $query = self::query();
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['sort']) && in_array($filters['sort'], ['id', 'created_at', 'updated_at'])) {
            $query->orderBy($filters['sort'], $filters['order'] ?? 'asc');
        }
        $page = $filters['page'] ?? 1;
        $size = $filters['size'] ?? 100;
        $carts = $query->paginate($size, ['*'], 'page', $page);
        return [
            'status' => 'success',
            'message' => '',
            'current_page' => $carts->currentPage(),
            'per_page' => $carts->perPage(),
            'total' => $carts->total(),
            'last_page' => $carts->lastPage(),
            'next_page' => ($carts->currentPage() == $carts->lastPage()) ? false : true,
            'data' => $carts->items()
        ];
    }

    public function getContent()
    {
        return $this->load('items.product');
    }

    public function addItem($productId, $quantity)
    {
        $product = Product::find($productId);
        if (!$product || $product->stock < $quantity) {
            return [
                'status' => 'error',
                'message' => 'Ürün bulunamadı veya stok yetersiz'];
        }

        $cartItem = $this->items()->firstOrCreate(
            ['product_id' => $productId],
            ['price' => $product->price]
        );
        $cartItemQuantity = $cartItem->quantity + $quantity;
        if($product->stock < $cartItemQuantity) {
            return [
                'status' => 'error',
                'message' => 'Stok yetersiz'
            ];
        }
        $cartItem->quantity = $cartItemQuantity;
        $cartItem->save();
        return [
            'status' => 'success',
            'message' => 'Ürün sepete eklendi'
        ];
    }

    public function updateItemQuantity($itemId, $quantity)
    {
        $cartItem = $this->items()->find($itemId);
        if($cartItem) {
            $product = $cartItem->product;
            if($product->stock < $quantity) {
                return false;
            }
            $cartItem->quantity = $quantity;
            $cartItem->save();
            return true;
        }
        return false;
    }

    public function removeItem($itemId)
    {
        return $this->items()->where('id', $itemId)->delete();
    }
}
