<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    public function tree()
    {
        $categoryModel = new Category();
        $categoryTree = $categoryModel->getAllCategoriesByTree();
        return response()->json(['status' => 'success', 'message' => 'Kategori ağacı başarıyla getirildi.' , 'data' => $categoryTree]);
    }

    public function show($id)
    {
        $category = Category::find($id);
        if (!$category)
        {
            return response()->json(['status' => 'error', 'message' => 'Kategori bulunamadı'], 404);
        }
        return response()->json($category);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->is_admin)
        {
            return response()->json(['status' => 'error', 'message' => 'Yetkiniz bulunmamaktadır!'], 403);
        }
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);
        $categoryExists = Category::categoryExists($validatedData['name'], $validatedData['parent_id']);
        if ($categoryExists)
        {
            return response()->json(['status'=> 'error', 'message' => 'Bu kategori zaten mevcut'], 400);
        }
        $parentCategory = null;
        if($validatedData['parent_id'] != null && $validatedData['parent_id'] != 0 && $validatedData['parent_id'] != '')
        {
            $parentCategory = Category::find($validatedData['parent_id']);
            if (!$parentCategory)
            {
                return response()->json(['status'=> 'error','message' => 'Üst kategori bulunamadı'], 404);
            }
            $validatedData['path'] = $parentCategory->path . '/' . $validatedData['name'];
            $validatedData['is_leaf'] = true;
            $validatedData['is_root'] = false;
            $parentCategory->is_leaf = false;
            $updateParentCategory = Category::updateCategory($parentCategory->toArray());
            if(!$updateParentCategory)
            {
                return response()->json(['status'=> 'error', 'message' => 'Üst kategori güncellenemedi'], 500);
            }
        }else
        {
            $validatedData['path'] = $validatedData['name'];
            $validatedData['is_leaf'] = false;
            $validatedData['is_root'] = true;
        }
        $validatedData['is_active'] = true;
        $validatedData['parent_id'] = $parentCategory ? $parentCategory->id : null;
        $validatedData['display_name'] = $validatedData['display_name'] != '' && $validatedData['display_name'] != null ? $validatedData['display_name'] : $validatedData['name'];
        $category = Category::createCategory($validatedData);
        if(!$category)
        {
            return response()->json(['status'=> 'error', 'message' => 'Kategori oluşturulamadı'], 500);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Kategori başarıyla oluşturuldu',
            'data' => $category
        ], 201);
    }


    public function destroy($id)
    {
        if (!auth()->user()->is_admin) {
            return response()->json(['status'=> 'error', 'message' => 'Yetkiniz bulunmamaktadır!'], 403);
        }
        $category = Category::find($id);
        if (!$category)
        {
            return response()->json(['status'=> 'error', 'message' => 'Kategori bulunamadı'], 404);
        }
        if($category->is_root)
        {
            return response()->json(['status'=> 'error', 'message' => 'Kök kategori silinemez'], 400);
        }
        if($category->is_leaf){
            $parentCategory = Category::find($category->parent_id);
            $parentCategory->is_leaf = true;
            $updateParentCategory = Category::updateCategory($parentCategory->toArray());
            if(!$updateParentCategory)
            {
                return response()->json(['status'=> 'error', 'message' => 'Üst kategori güncellenemedi'], 500);
            }
        }
        $category->delete();
        return response()->json(['status'=> 'success', 'message' => 'Kategori başarıyla silindi']);
    }
}
