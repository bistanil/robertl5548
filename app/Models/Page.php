<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{	
	use \Rutorika\Sortable\SortableTrait;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'caption', 'meta_title','meta_keywords','meta_description','slug', 'content', 'active', 'language', 'parent', 'position','menu'
    ];

    protected static $sortableGroupField = 'parent';

    public function bySlug($slug)
    {
        return $this->whereSlug($slug)->first();
    }

    public static function subpages($slug)
    {
        $id=Page::where('slug',$slug)->first()->id;
        return Page::sorted()->where('parent', $id)->paginate();
    }

}
