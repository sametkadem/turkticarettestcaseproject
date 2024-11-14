<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $validatedData = $request->validate([
            'categoryId' => 'nullable|exists:categories,id',
            'search' => 'nullable|string|max:255',
            'minPrice' => 'nullable|numeric|min:0',
            'maxPrice' => 'nullable|numeric|min:0',
            'minStock' => 'nullable|integer|min:0',
            'maxStock' => 'nullable|integer|min:0',
            'sort' => 'nullable|string|in:price,stock',
            'order' => 'nullable|string|in:asc,desc',
            'page' => 'nullable|integer|min:1',
            'size' => 'nullable|integer|min:1|max:1000',
        ]);
        $products = Product::getProducts($validatedData);
        return response()->json(['status'=> 'success', 'message' => ''], 404);
    }

    public function show($id)
    {
        $product = Product::getProductById($id);
        if (!$product)
        {
            return response()->json(['status'=> 'error', 'message' => 'Ürün bulunamadı.'], 404);
        }
        return response()->json($product);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->is_admin) {
            return response()->json(['status'=> 'error', 'message' => 'Yetkiniz bulunmamaktadır!'], 403);
        }
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'categoryId' => 'required|exists:categories,id',
        ]);
        if (!Product::categoryExists($validatedData['categoryId']))
        {
            return response()->json(['status' => 'error', 'message' => 'Kategori bulunamadı!'], 404);
        }

        $product = Product::createProduct($validatedData);
        return response()->json($product, 201);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->is_admin) {
            return response()->json(['status'=> 'error', 'message' => 'Yetkiniz bulunmamaktadır!'], 403);
        }
        $product = Product::getProductById($id);
        if (!$product)
        {
            return response()->json(['error' => 'Product not found'], 404);
        }
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'categoryId' => 'sometimes|required|exists:categories,id',
        ]);
        $product->updateProduct($validatedData);
        return response()->json($product);
    }

    public function destroy($id)
    {
        if (!auth()->user()->is_admin) {
            return response()->json(['status'=> 'error', 'message' => 'Yetkiniz bulunmamaktadır!'], 403);
        }
        $product = Product::getProductById($id);
        if (!$product)
        {
            return response()->json(['status'=>'error', 'message' => 'Ürün bulunamadı!'], 404);
        }
        $product->deleteProduct();
        return response()->json(['status'=>'success', 'message' => 'Ürün başarıyla silindi!']);
    }
}
