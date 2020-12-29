<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
use LaravelLocalization;

class Category extends Model
{
    use NodeTrait;

    protected $table    = 'categories';

    protected $fillable = ['name', 'category_input_id', 'physical_quantity_id', 'parent_id', 'description', 'source', 'icon', 'type', 'required'];
	protected $guarded 	= ['id'];

	protected $hidden   = ['created_at','updated_at', 'category_input_id', 'physical_quantity_id','_lft','_rgt','pivot','input_type','options','old_id'];

    protected $appends  = ['input','trans','unit']; //'parent'

    public static $types= 
    [
        'checklist' => 'Hive checklist item',
        'research' => 'Research specific item',
        'system' => 'System (required for interface)',
    ]; 

    // Relations
    public function getInputAttribute()
    {
        if ($this->category_input_id != null)
        {
            $type = CategoryInput::where('id', $this->category_input_id)->value('type');
            if ($type)
                return $type;
        }
        return null;
    }

    public function getTranslationsAttribute()
    {
        return $this->translations();
    }

    public function getTextAttribute()
    {
        return $this->transName();
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
            //die(print_r(['anc'=>$ancTypeArr, 'cat'=>$this->name]));

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

    public function inputRange()
    {
        $type = $this->inputType;
        $range= [];
        if (isset($type))
        {
            if (isset($type->min)) array_push($range,  __('crud.min').': '.$type->min);
            if (isset($type->max)) array_push($range,  __('crud.max').': '.$type->max);
        }
        return count($range) == 0 ? null : implode(' - ', $range);
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
        return Translation::translateArray($this->name);
    }

    public function transName($locale = null)
    {
        $trans = Translation::translate($this->name);
        return isset($trans) ? $trans : $this->name;
    }

    public function ancName($locale = null, $sep = " > ")
    {
        if ($locale == null)
            $locale = LaravelLocalization::getCurrentLocale();
        
        $ancest = $this->getAncestors()->map(function($item,$key) use ($locale, $sep){
            return $item->transName($locale).$sep;
        });
        return $ancest->implode('');
    }

    public function rootName($locale = null)
    {
        if ($locale == null)
            $locale = LaravelLocalization::getCurrentLocale();

        $ancest = $this->getAncestors();
        if ($ancest->count() > 0)
            return $ancest->get(0)->transName($locale);

        return "";
    }

    public function rootNodeName()
    {
        $ancest = $this->getAncestors();
        if ($ancest->count() > 0)
            return $ancest->get(0)->name;

        return "";
    }

    public function useAmount()
    {
        $i = InspectionItem::where('category_id', $this->id)->count();
        $v = Hive::where('hive_type_id', $this->id)->count();
        $h = HiveLayer::where('category_id', $this->id)->count();
        $f = HiveLayerFrame::where('category_id', $this->id)->count();
        $l = Location::where('category_id', $this->id)->count();
        $s = Device::where('category_id', $this->id)->count();
        $p = Production::where('category_id', $this->id)->count();
        $q = Queen::where('race_id', $this->id)->count();
        $o = isset($this->old_id) ? 1 : 0;

        $total_usage = $i+$v+$h+$f+$l+$s+$p+$q+$o;

        return $total_usage;
    }

    // // finding on ::type()
    // public function scopeType() 
    // {
    //     return $this->inputTypeType;
    // }

    // //finding on ::name()
    // public function scopeName($query, $name) 
    // {
    //     return $query->where('name', $name);
    // }
    // // finding on ::name()
    // public function scopeTypeName($query, $type, $name) 
    // {
    //     return $query->where('type', $type)->where('name', $name);
    // }

    //finding on ::child()
    public static function findCategoryByParentAndName($parent_name, $name) 
    {
        //$parent = Category::whereJoin('name', $parent_name)->whereJoin('children.name', $name)->first();
        $parent = Category::where('name', $parent_name)->first();

        if (isset($parent))
            return $parent->children()->where('name', $name)->first();

        return new Category;
    }

    public static function findCategoryIdByParentAndName($parent_name, $name)
    {
        $cat = Category::findCategoryByParentAndName($parent_name, $name);
        if (isset($cat))
            return $cat->id;

        return null;
    }

    public static function descendentsByRootParentAndName($root_name, $parent_name, $name, $whereTypeIn=['system']) 
    {
        $category = Category::findCategoryByRootParentAndName($root_name, $parent_name, $name, $whereTypeIn);
        //die(print_r($category->toArray()));
        if (isset($category))
            return Category::whereDescendantOf($category)->get();

        return [];
    }

    public static function findCategoryByRootParentAndName($root_name, $parent_name, $name, $whereTypeIn=['system']) 
    {
        $root   = Category::whereIsRoot()->where('name', $root_name)->first();
        $parent = Category::descendantsAndSelf($root)->whereIn('type', $whereTypeIn)->where('name', $parent_name)->first();
        if (isset($parent))
            return $parent->children()->where('name', $name)->first();

        return new Category;
    }

    public static function findCategoryIdByRootParentAndName($root_name, $parent_name, $name, $whereTypeIn=['system'])
    {
        $cat = Category::findCategoryByRootParentAndName($root_name, $parent_name, $name, $whereTypeIn);
        if (isset($cat))
            return $cat->id;

        return null;
    }

    public static function getRootIds($order=true)
    {
        $locale = LaravelLocalization::getCurrentLocale();

        if ($order)
            return Category::whereIsRoot()->whereNotIn('type', ['system'])->get()->sortBy("trans.$locale", SORT_NATURAL|SORT_FLAG_CASE)->pluck('id')->toArray();

        return Category::whereIsRoot()->whereNotIn('type', ['system'])->get()->pluck('id')->toArray();
    }

    public static function getTaxonomy($rootNodes=null, $order=true, $flat=false)
    {
        $locale = LaravelLocalization::getCurrentLocale();

        if (gettype($rootNodes) !== 'array' || count($rootNodes) == 0)
        {
            if ($order === true)
                $rootNodes = Category::whereIsRoot()->whereNotIn('type', ['system'])->get()->sortBy("trans.$locale", SORT_NATURAL|SORT_FLAG_CASE)->pluck('id');
            else
                $rootNodes = Category::whereIsRoot()->whereNotIn('type', ['system'])->pluck('id');

        }
        
        $taxonomy = collect(); 
        foreach ($rootNodes as $node)
        {
            if ($flat == true && ($order === null || $order === false))
                $taxonomy = $taxonomy->merge(Category::whereNotIn('type', ['system'])->descendantsAndSelf($node) );
            else if ($flat == true && $order === true)
                $taxonomy = $taxonomy->merge(Category::whereNotIn('type', ['system'])->descendantsAndSelf($node)->sortBy("trans.$locale", SORT_NATURAL|SORT_FLAG_CASE) );
            else if ($flat == false && $order === null)
                $taxonomy = $taxonomy->merge(Category::whereNotIn('type', ['system'])->descendantsAndSelf($node)->toTree() );
            else if ($flat == false)
            {
                if (gettype($order) == 'array' && count($order) > 0)
                    $taxonomy = $taxonomy->merge(Category::descendantsAndSelf($node)->whereNotIn('type', ['system'])->sortBy(function($cat, $key) use ($order) { return array_search($cat->id, $order); })->toTree());
                else
                    $taxonomy = $taxonomy->merge(Category::descendantsAndSelf($node)->whereNotIn('type', ['system'])->sortBy("trans.$locale", SORT_NATURAL|SORT_FLAG_CASE)->toTree());
            }
        }

        return $taxonomy;
    }


}
