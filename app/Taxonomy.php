<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
use LaravelLocalization;
use Illuminate\Support\Facades\DB;

class Taxonomy extends Model
{
    // This model is used for the creation of the object tree for the JSTree elements

    use NodeTrait;

    protected $table    = 'categories';

    protected $fillable = ['name', 'category_input_id', 'physical_quantity_id', 'parent_id', 'description', 'source', 'icon', 'type'];
	protected $guarded 	= ['id'];

	protected $hidden   = ['created_at','updated_at', 'category_input_id', 'physical_quantity_id','_lft','_rgt','pivot','old_id','description','type','input_type','options','parent_id', 'source','name','required'];

    protected $appends  = ['icon','text'];

    public static $types= 
    [
        'checklist' => 'Hive checklist item',
        'research' => 'Research specific item',
        'system' => 'System (required for interface)',
    ]; 

    // Relations
    public function getIconAttribute()
    {
        if ($this->category_input_id != null)
        {
            $icon = CategoryInput::where('id', $this->category_input_id)->first()->glyphIcon();
            if ($icon)
                return "glyphicon glyphicon-$icon";
        }
        return null;
    }

    public function getTranslationsAttribute()
    {
        return $this->translations();
    }

    public function getTextAttribute()
    {
        $req = ($this->required) ? ' *' : '';
        return $this->transName().$req;
    }

    public function getParentAttribute()
    {
        return isset($this->parent_id) ? $this->parent_id : '#'; // '#' for jsTree
    }

    public function getTransAttribute()
    {
        return $this->trans();
    }

    public function getUnitAttribute()
    {
        if ($this->physical_quantity_id != null)
        {
            $unit = $this->physicalQuantity()->value('abbreviation');
            if ($unit)
                return $unit;
        }
        return null;
    }
   
    public function physicalQuantity()
    {
        return $this->hasOne(PhysicalQuantity::class, 'id', 'physical_quantity_id');
    }

    public function checklists()
    {
        return $this->belongsToMany(Checklist::class, 'checklist_category');
    }

    public function inputType()
    {
        return $this->hasOne(CategoryInput::class, 'id', 'category_input_id');
    }


    public function isSystem()
    {
        if ($this->type == 'system')
            return true;

        $ancTypeArr = $this->ancestors->pluck('type')->toArray();

        if (in_array('system', $ancTypeArr))
            return true;

        return false;
    }

    public function physicalQuantityId()
    {
        $quan = $this->physicalQuantity;
        if (isset($quan))
            return $quan->id;

        return null;
    }

    public function inputTypeName()
    {
        $type = $this->inputType;
        if (isset($type))
            return $type->name;

        return '';
    }

    public function inputTypeType()
    {
        $type = $this->inputType;
        if (isset($type))
            return $type->type;

        return '';
    }

    public function inputTypeIcon()
    {
        $type = $this->inputType;
        if (isset($type))
            return $type->glyphIcon();

        return '';
    }

    public function inputTypeId()
    {
        $type = $this->inputType;
        if (isset($type))
            return $type->id;

        return null;
    }

    public function translation($language_abbr)
    {
        $lang_id = Language::where('abbreviation', $language_abbr)->pluck('id');
        if ($lang_id)
            return Translation::where('language_id', $lang_id)->where('name', $this->name)->value('translation');
        
        return $this->name;
    }

    public function translations()
    {
        $trans = Translation::where('name', $this->name)->pluck('translation','language_id');
        if ($trans)
            return $trans;
        
        return [['translation'=>$this->name, 'language_id'=>0]];
    }

    public function trans()
    {
        $trans = DB::table('translations')
                    ->join('languages', 'translations.language_id', '=', 'languages.id')
                    ->where('translations.name', $this->name)
                    ->select('translations.translation', 'languages.twochar')
                    ->get();

        if ($trans)
        {
            $out = [];
            foreach($trans as $item)
            {
                $out[$item->twochar] = $item->translation; 
            }
            return $out;
        }
        
        return null;
    }

    public function transName($locale = null)
    {
        if ($locale == null)
            $locale = LaravelLocalization::getCurrentLocale();
        
        $trans = $this->trans;
        return isset($trans[$locale]) ? $trans[$locale] : $this->name;
    }

    public function ancName($locale = null)
    {
        if ($locale == null)
            $locale = LaravelLocalization::getCurrentLocale();
        
        $ancest = $this->getAncestors()->map(function($item,$key) use ($locale){
            return $item->transName($locale).' > ';
        });
        return $ancest->implode('');
    }

    public function useAmount()
    {
        $i = InspectionItem::where('category_id', $this->id)->get()->count();
        $h = HiveLayer::where('category_id', $this->id)->get()->count();
        $f = HiveLayerFrame::where('category_id', $this->id)->get()->count();
        $l = Location::where('category_id', $this->id)->get()->count();
        $s = Sensor::where('category_id', $this->id)->get()->count();
        $p = Production::where('category_id', $this->id)->get()->count();

        $total_usage = $i+$h+$f+$l+$s+$p;

        return $total_usage;
    }

    public static function descendentsByRootParentAndName($root_name, $parent_name, $name, $whereTypeIn=['system']) 
    {
        $category = Taxonomy::findCategoryByRootParentAndName($root_name, $parent_name, $name, $whereTypeIn);
        //die(print_r($category->toArray()));
        if (isset($category))
            return Taxonomy::whereDescendantOf($category)->get();

        return [];
    }

    public static function findCategoryByRootParentAndName($root_name, $parent_name, $name, $whereTypeIn=['system']) 
    {
        $root   = Taxonomy::whereIsRoot()->where('name', $root_name)->first();
        $parent = Taxonomy::descendantsAndSelf($root)->whereIn('type', $whereTypeIn)->where('name', $parent_name)->first();
        if (isset($parent))
            return $parent->children()->where('name', $name)->first();

        return new Taxonomy;
    }

    public static function findCategoryIdByRootParentAndName($root_name, $parent_name, $name, $whereTypeIn=['system'])
    {
        $cat = Taxonomy::findCategoryByRootParentAndName($root_name, $parent_name, $name, $whereTypeIn);
        if (isset($cat))
            return $cat->id;

        return null;
    }

    public static function getRootIds($order=true)
    {
        $locale = LaravelLocalization::getCurrentLocale();

        if ($order)
            return Taxonomy::whereIsRoot()->whereNotIn('type', ['system'])->get()->sortBy("trans.$locale", SORT_NATURAL|SORT_FLAG_CASE)->pluck('id')->toArray();

        return Taxonomy::whereIsRoot()->whereNotIn('type', ['system'])->get()->pluck('id')->toArray();
    }

    // Defines JSON tree for JSTree. See: https://www.jstree.com/docs/json/
    public static function getTaxonomy($rootNodes=null, $order=true, $flat=false)
    {
        $locale = LaravelLocalization::getCurrentLocale();

        if (gettype($rootNodes) !== 'array' || count($rootNodes) == 0)
        {
            if ($order === true)
                $rootNodes = Taxonomy::whereIsRoot()->whereNotIn('type', ['system'])->get()->sortBy("trans.$locale", SORT_NATURAL|SORT_FLAG_CASE)->pluck('id');
            else
                $rootNodes = Taxonomy::whereIsRoot()->whereNotIn('type', ['system'])->pluck('id');
        }
        
        $taxonomy = collect(); 
        foreach ($rootNodes as $node)
        {
            if ($flat == true && $order === false)
                $taxonomy = $taxonomy->merge(Taxonomy::whereNotIn('type', ['system'])->descendantsAndSelf($node) );
            else if ($flat == true && $order === true)
                $taxonomy = $taxonomy->merge(Category::whereNotIn('type', ['system'])->descendantsAndSelf($node)->sortBy("trans.$locale", SORT_NATURAL|SORT_FLAG_CASE) );
            else if ($flat == false && $order === false)
                $taxonomy = $taxonomy->merge(Taxonomy::whereNotIn('type', ['system'])->descendantsAndSelf($node)->toTree() );
            else if ($flat == false)
            {
                if (gettype($order) == 'array' && count($order) > 0)
                    $taxonomy = $taxonomy
                        ->merge(Taxonomy::descendantsAndSelf($node)
                        ->whereNotIn('type', ['system'])
                        ->map(function($cat, $key) use ($order) { $selected = in_array($cat->id, $order); $cat->state = ['selected'=>$selected, 'opened'=>$selected, 'cat'=>$cat->id, 'disabled'=>$cat->required]; return $cat; })
                        ->sortBy(function($cat, $key) use ($order) { return array_search($cat->id, $order); })
                        ->toTree());
                else
                    $taxonomy = $taxonomy->merge(Taxonomy::descendantsAndSelf($node)->whereNotIn('type', ['system'])->sortBy("trans.$locale", SORT_NATURAL|SORT_FLAG_CASE)->toTree());
            }
        }

        return $taxonomy;
    }


}
