<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    private $Category;
    public function __construct()
    {
        $this->Category = new Category();
    }
    public function index(Request $request)
    {
        $validatedData = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
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
        return response()->json($products, 200);
    }

    public function show($id)
    {
        $product = Product::getProductById($id);
        if (!$product)
        {
            return response()->json(['status'=> 'error', 'message' => 'Ürün bulunamadı.'], 404);
        }
        return response()->json(['status'=> 'success', 'data' => $product], 200);
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
            'category_id' => 'required|exists:categories,id',
        ]);
        $categoryExists = $this->Category->categoryExistsById($validatedData['category_id']);
        if (!$categoryExists)
        {
            return response()->json(['status' => 'error', 'message' => 'Kategori bulunamadı!'], 404);
        }
        $exists = Product::productExists($validatedData['name'], $validatedData['category_id']);
        if($exists)
        {
            return response()->json(['status' => 'error', 'message' => 'Bu ürün zaten mevcut!'], 400);
        }
        $category = $this->Category->getCategoryById($validatedData['category_id']);
        if(!$category->is_leaf)
        {
            return response()->json(['status' => 'error', 'message' => 'Ürün eklemek için yaprak kategori seçmelisiniz!'], 400);
        }
        $validatedData['category_id'] = $category->id;
        $product = Product::createProduct($validatedData);
        if(!$product)
        {
            return response()->json(['status' => 'error', 'message' => 'Ürün oluşturulamadı!'], 500);
        }
        return response()->json(['status' => 'success', 'message' => 'Ürün başarıyla oluşturuldu!', 'data' => $product], 201);
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
