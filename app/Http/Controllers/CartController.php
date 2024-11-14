<?php
namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function getCart()
    {
        $cart = Cart::getUserCart(auth()->id());
        $cartitems = $cart->items;
        $totalAmount = 0;
        foreach ($cartitems as &$item)
        {
            $totalAmount += $item->price * $item->quantity;
            $item['item_total'] = $item->price * $item->quantity;
        }
        $cart['total_amount'] = $totalAmount;
        $cart['items'] = $cartitems;
        return response()->json(['status' => 'success', 'data' => $cart]);
    }
    public function getCartList(Request $request)
    {
        $validatedData = $request->validate([
            'status' => 'nullable|string|in:created,ordered',
            'sort' => 'nullable|string|in:id,created_at,updated_at,status',
            'order' => 'nullable|string|in:asc,desc',
            'page' => 'nullable|integer|min:1',
            'size' => 'nullable|integer|min:1|max:1000',
        ]);
        $userid = auth()->id();
        $validatedData['user_id'] = $userid;
        $carts = Cart::getCarts($validatedData);
        foreach ($carts['data'] as &$cart)
        {
            $cartitems = $cart->items;
            $totalAmount = 0;
            foreach ($cartitems as &$item)
            {
                $totalAmount += $item->price * $item->quantity;
                $item['item_total'] = $item->price * $item->quantity;
            }
            $cart['total_amount'] = $totalAmount;
            $cart['items'] = $cartitems;
        }
        return response()->json($carts, 200);
    }

    public function addItem(Request $request)
    {
        $validatedData = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
        $cart = Cart::getUserCart(auth()->id());
        $product = Product::getProductById($validatedData['product_id']);
        if (!$product)
        {
            return response()->json(['status' => 'error', 'message' => 'Ürün bulunamadı.'], 404);
        }
        $addCartItem = $cart->addItem($validatedData['product_id'], $validatedData['quantity']);
        if ($addCartItem['status'] === 'error')
        {
            return response()->json($addCartItem, 400);
        }
        return response()->json(['status' => 'success', 'message' => 'Ürün sepete eklendi.']);
    }

    public function updateItem(Request $request, $id)
    {
        $validatedData = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);
        $cart = Cart::getUserCart(auth()->id());
        $cartItem = $cart->items()->where('id', $id)->first();
        if(!$cartItem)
        {
            return response()->json(['status' => 'error', 'message' => 'Sepet öğesi bulunamadı.'], 404);
        }
        $product = Product::getProductById($cartItem->product_id);
        if (!$product)
        {
            return response()->json(['status' => 'error', 'message' => 'Ürün bulunamadı.'], 404);
        }
        $maxStock = $product->stock;
        if ($validatedData['quantity'] > $maxStock)
        {
            return response()->json(['status' => 'error', 'message' => 'Yetersiz stok.'], 400);
        }
        if ($cart->updateItemQuantity($id, $validatedData['quantity']))
        {
            return response()->json(['status' => 'success', 'message' => 'Ürün miktarı güncellendi.']);
        }
        else
        {
            return response()->json(['status' => 'error', 'message' => 'Sepet öğesi bulunamadı.'], 404);
        }
    }

    public function removeItem($id)
    {
        $cart = Cart::getUserCart(auth()->id());
        if ($cart->removeItem($id))
        {
            return response()->json(['status' => 'success', 'message' => 'Ürün sepetten çıkarıldı.']);
        }
        else
        {
            return response()->json(['status' => 'error', 'message' => 'Sepet öğesi bulunamadı.'], 404);
        }
    }
}
