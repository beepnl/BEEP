<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Category;
use App\Taxonomy;
use Auth;

use Illuminate\Support\Facades\DB;

class Checklist extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'checklists';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'type', 'description'];

    protected $hidden   = ['pivot','deleted_at', 'users'];

    protected $appends  = ['category_ids', 'required_ids', 'owner', 'researches'];
    
    // check for deletion of linked items
    protected static function boot() {
        parent::boot();
        
        static::deleting(function($item) 
        {
            DB::table('checklist_category')->where('checklist_id', $item->id)->delete();
            DB::table('checklist_user')->where('checklist_id', $item->id)->delete();
            DB::table('checklist_hive')->where('checklist_id', $item->id)->delete();
        });
    }

    public function getCategoryIdsAttribute()
    {
        return $this->categoryIdArray();   
    }

    public function getRequiredIdsAttribute()
    {
        return $this->categories()->where('required', '=', true)->pluck('id')->toArray();
    }

    public function getOwnerAttribute()
    {
        return $this->users->contains(Auth::user());   
    }

    public function getResearchesAttribute()
    {
        return $this->researches()->pluck('name');   
    }



    public function categories()
    {
        return $this->belongsToMany(Category::class, 'checklist_category')->withPivot('order')->orderBy('order');
    }

    public function categoryIdArray()
    {
        return $this->categories()->pluck('id')->toArray();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'checklist_user');
    }

    public function hives()
    {
        return $this->belongsToMany(Hive::class, 'checklist_hive');
    }

    public function researches()
    {
        return $this->belongsToMany(Research::class, 'checklist_research');
    }
    
    public function getOrderedChecklist($selectedCatIds=[], $includeNotSelected=true)
    {
        $taxonomyRootIds = Taxonomy::getRootIds();
        $cheklistRootIds = array_intersect($selectedCatIds, $taxonomyRootIds); // order root nodes by defined order in checklist 
        if ($includeNotSelected)
            $cheklistRootIds = array_merge($cheklistRootIds, array_diff($taxonomyRootIds, $cheklistRootIds)); // add rest of (not selected categories)

        $taxonomy = Taxonomy::getTaxonomy($cheklistRootIds, $selectedCatIds);
        return $taxonomy;
    }

    public function syncCategories($categories)
    {
        if (isset($categories) && count($categories) > 0)
        {
            $cat_with_order = array_flip($categories);
            foreach ($cat_with_order as $cat_id => $value) 
            {
                $parents = Taxonomy::ancestorsOf($cat_id)->whereNotIn('type',['system'])->pluck('id')->toArray();
                if (count($parents) > 0)
                {
                    foreach ($parents as $p) 
                    {
                        if (in_array($p, $categories) == false)
                            $cat_with_order[$p] = ['order'=>$value];
                    }
                }
                $cat_with_order[$cat_id] = ['order'=>$value];
            }
            if (count($categories) > 0)
            {
                $this->categories()->sync($cat_with_order);
                $this->touch(); // set Checklist updated_at
                return true;
            }
        }
        return false;
    }

    public static function selectList()
    {
        return Checklist::orderBy('name')->pluck('name','id');
    }
}
