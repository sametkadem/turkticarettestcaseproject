<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        $validatedData = $request->validate([
            'cart_id' => 'required|exists:carts,id',
        ]);

        $user = auth()->user();
        $cart = Cart::where('id', $validatedData['cart_id'])->where('user_id', $user->id)->first();
        if (!$cart || $cart->status !== 'created') {
            return response()->json(['status'=> 'error', 'message' => 'Geçersiz sepet'], 400);
        }

        $cartItems = $cart->items;
        foreach ($cartItems as $item) {
            $product = $item->product;
            if ($product->stock < $item->quantity) {
                return response()->json(['status'=> 'error', 'message' => 'Stok yetersiz'], 400);
            }
            $product->stock -= $item->quantity;
            $product->save();
        }

        $totalAmount = $cart->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $order = Order::createOrder($user->id, $cart->id, $totalAmount);

        $cart->update(['status' => 'ordered']);

        return response()->json(['status'=> 'success', 'data' => $order], 201);
    }

    public function getOrders(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'nullable|integer',
            'cart_id' => 'nullable|integer',
            'status' => 'nullable|string|in:created,ordered',
            'minPrice' => 'nullable|numeric|min:0',
            'maxPrice' => 'nullable|numeric|min:0',
            'sort' => 'nullable|string|in:total_amount,created_at,updated_at',
            'order' => 'nullable|string|in:asc,desc',
            'page' => 'nullable|integer|min:1',
            'size' => 'nullable|integer|min:1|max:1000',
        ]);

        $user = auth()->user();
        $validatedData['user_id'] = $user->id;
        $orders = Order::getOrders($validatedData);
        return response()->json($orders);
    }

    public function getOrder($id)
    {
        $user = auth()->user();
        $order = Order::where('id', $id)->where('user_id', $user->id)->with('cart.items.product')->first();
        if (!$order) {
            return response()->json(['error' => 'Sipariş bulunamadı'], 404);
        }
        return response()->json($order);
    }
}
