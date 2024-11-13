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
    ];

    // Tüm ürünleri listele
    public static function getAllProducts()
    {
        return self::all();
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
