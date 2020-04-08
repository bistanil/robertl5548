<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;
use DB;

class PartsCategory extends Model
{
    //
    use \Rutorika\Sortable\SortableTrait;
    use \Rutorika\Sortable\BelongsToSortedManyTrait;

    protected $fillable = [
        'title', 'meta_title','meta_keywords','meta_description','slug', 'content', 'active', 'language', 'parent', 'position', 'terms', 'group','first_page','show_in_menu'
    ];

    protected static $sortableGroupField = 'parent';

    public function bySlug($slug)
    {
        return $this->whereSlug($slug)->first();
    }

    public static function subcategories($slug)
    {
        $id=PartsCategory::where('slug',$slug)->first()->id;
        return PartsCategory::sorted()->where('parent', $id)->paginate();
    }

    public function noTypeActiveSubcategories($slug)
    {
        $category = $this->bySlug($slug);        
        return collect(DB::select(DB::raw("SELECT parts_categories.slug,
                                                 parts_categories.image,
                                                 parts_categories.title,
                                                 parts_categories.parent,
                                                 parts_categories.id
                                    FROM parts_categories
                                    WHERE parts_categories.parent=".$category->id." AND parts_categories.active = 'active' ORDER BY position")));
    }  

    public function activeSubcategories($slug)
    {
        $category = $this->bySlug($slug);
        return collect(DB::select(DB::raw("SELECT parts_categories.slug,
                                                 parts_categories.image,
                                                 parts_categories.title,
                                                 parts_categories.parent,
                                                 parts_categories.id
                                    FROM parts_categories
                                    INNER JOIN type_categories ON parts_categories.id=type_categories.category_id AND parts_categories.parent=".$category->id." AND type_categories.type_id=".session()->get('type')->id." AND parts_categories.active = 'active' ORDER BY position")));
    }
}
