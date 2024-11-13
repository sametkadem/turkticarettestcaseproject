<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // GET /api/categories - Tüm kategorileri listele
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    // GET /api/categories/tree - Tüm kategorileri ağaç yapısında listele
    public function tree()
    {
        $categoryModel = new Category();
        $categoryTree = $categoryModel->getAllCategoriesByTree();
        return response()->json($categoryTree);
    }

    // GET /api/categories/{id} - Tek bir kategoriyi göster
    public function show($id)
    {
        $category = Category::find($id);
        if (!$category)
        {
            return response()->json(['error' => 'Kategori bulunamadı'], 404);
        }
        return response()->json($category);
    }

    // POST /api/categories - Yeni kategori oluştur
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'path' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_leaf' => 'boolean',
            'is_root' => 'boolean',
            'is_active' => 'boolean',
        ]);
        $category = Category::create($validatedData);
        return response()->json($category, 201);
    }

    // PUT /api/categories/{id} - Mevcut bir kategoriyi güncelle
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category)
        {
            return response()->json(['error' => 'Kategori bulunamadı'], 404);
        }
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'path' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_leaf' => 'boolean',
            'is_root' => 'boolean',
            'is_active' => 'boolean',
        ]);
        $category->update($validatedData);
        return response()->json($category);
    }

    // DELETE /api/categories/{id} - Kategoriyi sil
    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category)
        {
            return response()->json(['error' => 'Kategori bulunamadı'], 404);
        }
        $category->delete();
        return response()->json(['message' => 'Kategori başarıyla silindi']);
    }
}
