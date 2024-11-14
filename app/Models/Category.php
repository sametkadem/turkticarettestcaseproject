<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'path',
        'parent_id',
        'is_leaf',
        'is_root',
        'is_active'
    ];

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public static function createCategory($data)
    {
        return self::create($data);
    }

    public static function updateCategory($data)
    {
        return self::find($data['id'])->update($data);
    }

    public function deleteCategory()
    {
        return $this->delete();
    }

    public function getAllCategories()
    {
        return $this->all();
    }

    public function getCategoryById($id)
    {
        return $this->find($id);
    }

    public static function getCategory($filters)
    {
        $query = self::query();
        if (!empty($filters['category_id'])) {
            $query->where('id', $filters['category_id']);
        }
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['sort']) && in_array($filters['sort'], ['name', 'id', 'created_at', 'updated_at'])) {
            $query->orderBy($filters['sort'], $filters['order'] ?? 'asc');
        }
        $page = $filters['page'] ?? 1;
        $size = $filters['size'] ?? 100;
        $category = $query->paginate($size, ['*'], 'page', $page);
        return [
            'status' => 'success',
            'message' => '',
            'current_page' => $category->currentPage(),
            'per_page' => $category->perPage(),
            'total' => $category->total(),
            'last_page' => $category->lastPage(),
            'next_page' => ($category->currentPage() == $category->lastPage()) ? false : true,
            'data' => $category->items(),
        ];

    }

    // Kategori ağacını almak için recursive (özyinelemeli) bir fonksiyon
    public function getAllCategoriesByTree()
    {
        // Tüm kök (root) kategorileri getir
        $rootCategories = $this->where('is_root', 1)->where('is_active', 1)->get();
        // Her root kategorinin alt kategorilerini almak için recursive yapı oluştur
        $tree = $rootCategories->map(function($category)
        {
            return $this->buildCategoryTree($category);
        });
        return $tree;
    }

    // Kategori ve alt kategorilerini oluşturmak için recursive (özyinelemeli) bir yardımcı fonksiyon
    private function buildCategoryTree($category)
    {
        $category->subCategories = $this->where('parent_id', $category->id)->where('is_active', 1)->get()->map(function($subCategory)
            {
                return $this->buildCategoryTree($subCategory);
            });
        return $category;
    }

    public function categoryExists($name, $parentId)
    {
        return self::where('name', $name)->where('parent_id', $parentId)->exists();
    }

    public function categoryExistsById($id)
    {
        return self::where('id', $id)->exists();
    }
}
