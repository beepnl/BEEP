<?php
namespace App\Http\Controllers;

use App\Category;
use App\Http\Controllers\Controller;
use LaravelLocalization;

class TaxonomyController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	
    const BLACKLISTED_KEYS = ['id','required','icon'];

    private function removeKeys($array) 
    {
        $array = array_map(function($item) 
        {
            return is_array($item) ? $this->removeKeys($item) : $item;
        }, $array);

        return array_filter($array, function($value, $key) 
        {
            return !in_array($key, static::BLACKLISTED_KEYS, TRUE) && (!is_array($value) || count($value) > 0);
        }, ARRAY_FILTER_USE_BOTH);
    }

    public function display()
    {
        $fixed      = Category::fixTree(); // kalnoy/nestedset: to fix the tree to fill _lft and _rgt columns

        $locale 	= LaravelLocalization::getCurrentLocale();
        $locale_name= LaravelLocalization::getCurrentLocaleName();
        $categories = Category::getTaxonomy(null, false, true); // unordered flat

        $cat_names  = [];
        foreach ($categories as $id => $cat) 
        {
       		if ($cat->isSystem() == false)
       			$cat_names[' .\''.$cat->ancName($locale, '\'.\'').$cat->transName($locale).'\''] = $cat->rootNodeName();
        }

        ksort($cat_names);

        $cats       = [['id'=>' ','base'=>'']];
        foreach ($cat_names as $id => $base) 
        {
       		array_push($cats, ['id'=>$id, 'base'=>$base]);
        }

        //die(print_r($cats));
        
        $count		= $categories->count()-1;
        $catsJson 	= json_encode($cats, JSON_PRETTY_PRINT);
        
        // add filtered JSON tree
        $tree_base      = Category::getTaxonomy(null, true, false)->toArray(); // ordered tree
        $filtered_tree  = $this->removeKeys($tree_base);
        $filtered_json  = json_encode($filtered_tree, JSON_PRETTY_PRINT);

        return view('taxonomy.display', compact('catsJson','count','filtered_json','locale_name','fixed'));
    }



}
