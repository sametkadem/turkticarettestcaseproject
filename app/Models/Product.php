<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'stock',
        'category_id',
    ];

    // Tüm ürünleri listele
    public static function getAllProducts()
    {
        return self::all();
    }

    public static function getProducts($filters)
    {
        $query = self::query();
        if (!empty($filters['categoryId'])) {
            $query->where('category_id', $filters['categoryId']);
        }
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }
        if (isset($filters['minPrice'])) {
            $query->where('price', '>=', $filters['minPrice']);
        }
        if (isset($filters['maxPrice'])) {
            $query->where('price', '<=', $filters['maxPrice']);
        }
        if (isset($filters['minStock'])) {
            $query->where('stock', '>=', $filters['minStock']);
        }
        if (isset($filters['maxStock'])) {
            $query->where('stock', '<=', $filters['maxStock']);
        }
        if (!empty($filters['sort']) && in_array($filters['sort'], ['price', 'stock'])) {
            $query->orderBy($filters['sort'], $filters['order'] ?? 'asc');
        }
        $page = $filters['page'] ?? 1;
        $size = $filters['size'] ?? 100;
        $products = $query->paginate($size, ['*'], 'page', $page);
        return [
            'status' => 'success',
            'message' => '',
            'data' => $products->items(),
            'current_page' => $products->currentPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
            'last_page' => $products->lastPage(),
            'next_page' => ($products->currentPage() == $products->lastPage()) ? false : true,
        ];
    }

    // Tek bir ürünü bul
    public static function getProductById($id)
    {
        return self::find($id);
    }

    // Yeni bir ürün oluştur
    public static function createProduct($data)
    {
        return self::create($data);
    }

    // Mevcut bir ürünü güncelle
    public function updateProduct($data)
    {
        return $this->update($data);
    }

    // Ürünü sil
    public function deleteProduct()
    {
        return $this->delete();
    }
}
