<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // GET /api/products - Tüm ürünleri listele
    public function index()
    {
        $products = Product::getAllProducts();
        return response()->json($products);
    }

    // GET /api/products/{id} - Tek bir ürünü getir
    public function show($id)
    {
        $product = Product::getProductById($id);
        if (!$product)
        {
            return response()->json(['error' => 'Product not found'], 404);
        }
        return response()->json($product);
    }

    // POST /api/products - Yeni ürün oluştur (Admin only)
    public function store(Request $request)
    {
        $this->authorize('admin');
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);
        $product = Product::createProduct($validatedData);
        return response()->json($product, 201);
    }

    // PUT /api/products/{id} - Ürünü güncelle (Admin only)
    public function update(Request $request, $id)
    {
        $this->authorize('admin');
        $product = Product::getProductById($id);
        if (!$product)
        {
            return response()->json(['error' => 'Product not found'], 404);
        }
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
        ]);
        $product->updateProduct($validatedData);
        return response()->json($product);
    }

    // DELETE /api/products/{id} - Ürünü sil (Admin only)
    public function destroy($id)
    {
        $this->authorize('admin');
        $product = Product::getProductById($id);
        if (!$product)
        {
            return response()->json(['error' => 'Product not found'], 404);
        }
        $product->deleteProduct();
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
