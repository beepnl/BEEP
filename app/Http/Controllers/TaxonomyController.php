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
        $categories = Category::getTaxonomy(null, false, true, []); // unordered flat

        $cat_names      = [];
        $cat_ids_excl   = []; 
        $cat_names_excl = ['frame_2_side_a','frame_2_side_b','frame_3_side_a','frame_3_side_b','frame_4_side_a','frame_4_side_b','frame_5_side_a','frame_5_side_b','frame_6_side_a','frame_6_side_b','frame_7_side_a','frame_7_side_b','frame_8_side_a','frame_8_side_b','frame_9_side_a','frame_9_side_b','frame_10_side_a','frame_10_side_b','frame_11_side_a','frame_11_side_b','frame_12_side_a','frame_12_side_b','frame_13_side_a','frame_13_side_b','frame_14_side_a','frame_14_side_b','frame_15_side_a','frame_15_side_b','frame_16_side_a','frame_16_side_b','frame_17_side_a','frame_17_side_b','frame_18_side_a','frame_18_side_b','frame_19_side_a','frame_19_side_b','frame_20_side_a','frame_20_side_b','frame_21_side_a','frame_21_side_b','frame_22_side_a','frame_22_side_b','frame_23_side_a','frame_23_side_b','frame_24_side_a','frame_24_side_b'];

        foreach ($categories as $id => $cat)
        {
            if (in_array($cat->name, $cat_names_excl))
                $cat_ids_excl[] = $cat->id;
            else if (!in_array($cat->parent_id, $cat_ids_excl))
                $cat_names[' .'.$cat->ancName($locale, '.').$cat->transName($locale)] = ['base'=>$cat->rootNodeName(), 'parent'=>' .'.substr($cat->ancName($locale, '.'), 0, -1), 'name'=>$cat->name];
        }

        ksort($cat_names);

        $cats        = [['id'=>' ','base'=>'', 'parent'=>'']];
        $prev_id     = null;
        $prev_base   = null;
        $prev_parent = null;
        $extra_cats  = [];

        foreach ($cat_names as $id => $cat_array) 
        {
       		$base   = $cat_array['base'];
            $parent = $cat_array['parent'];

            if ($prev_parent != $parent && $id != $parent && $prev_id != $parent && $base != '') // some categories might be skipped
            {
                $parent_parent_parent = implode(".", explode(".", $parent, -2));
                if (!isset($cat_names[$parent_parent_parent]) && $parent_parent_parent != " " && $parent_parent_parent != "" && !in_array($parent_parent_parent, $extra_cats))
                {
                    array_push($cats, ['id'=>$parent_parent_parent, 'base'=>$base]);
                    $extra_cats[] = $parent_parent_parent;
                }

                $parent_parent = implode(".", explode(".", $parent, -1));
                if (!isset($cat_names[$parent_parent]) && $parent_parent != " " && $parent_parent != "" && !in_array($parent_parent, $extra_cats))
                {
                    array_push($cats, ['id'=>$parent_parent, 'base'=>$base]);
                    $extra_cats[] = $parent_parent;
                }

                if (!isset($cat_names[$parent]) && $parent != " " && $parent != "" && !in_array($parent, $extra_cats))
                {
                    array_push($cats, ['id'=>$parent, 'base'=>$base]);
                    $extra_cats[] = $parent;
                }

            }

            array_push($cats, ['id'=>$id, 'base'=>$base]);
            $prev_id   = $id;
            $prev_base = $base;
            $prev_parent = $parent;
        }

        //die(print_r($cats));
        
        $count		= count($cats)-1;
        $catsJson 	= json_encode($cats, JSON_PRETTY_PRINT);
        
        // add filtered JSON tree
        $tree_base      = Category::getTaxonomy(null, true, false)->toArray(); // ordered tree
        $filtered_tree  = $this->removeKeys($tree_base);
        $filtered_json  = json_encode($filtered_tree, JSON_PRETTY_PRINT);

        return view('taxonomy.display', compact('catsJson','count','filtered_json','locale_name','fixed'));
    }



}
