<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'code',
        'status',
        'imported_t',
        'url',
        'creator',
        'created_t',
        'last_modified_t',
        'product_name',
        'quantity',
        'brands',
        'categories',
        'labels',
        'cities',
        'purchase_places',
        'stores',
        'ingredients_text',
        'traces',
        'serving_size',
        'serving_quantity',
        'nutriscore_score',
        'nutriscore_grade',
        'main_category',
        'image_url',
    ];

    /**
     * Define o nome do índice no Algolia
     */
    public function searchableAs()
    {
        return 'products';
    }

    /**
     * Define quais atributos serão indexados
     */
    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'product_name' => $this->product_name,
            'brands' => $this->brands,
            'categories' => $this->categories,
            'ingredients_text' => $this->ingredients_text,
            'main_category' => $this->main_category,
            'status' => $this->status,
        ];
    }
    
    /**
     * Determine se este modelo deve ser indexado.
     *
     * @return bool
     */
    public function shouldBeSearchable()
    {
        // Por exemplo, indexar apenas produtos publicados
        return $this->status !== 'trash';
    }
}
