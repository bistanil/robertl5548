<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogCategory extends Model
{	
	use \Rutorika\Sortable\SortableTrait;
    use \Rutorika\Sortable\BelongsToSortedManyTrait;

    protected $table='catalog_categories';

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'meta_title','meta_keywords','meta_description','slug', 'content', 'active', 'language', 'parent', 'position'
    ];

    protected static $sortableGroupField = ['catalog_id', 'parent'];

    public function bySlug($slug)
    {
        return $this->whereSlug($slug)->first();
    }

    public static function subcategories($slug)
    {
        $id=CatalogCategory::where('slug',$slug)->first()->id;
        return CatalogCategory::sorted()->where('parent', $id);
    }

    public function catalog()
    {
        return $this->belongsTo('App\Models\Catalog');
    }

    public function products()
    {
        return $this->belongsToSortedMany('App\Models\CatalogProduct', 'position','catalog_category_product', 'category_id', 'product_id');
    }

}
