<?php

namespace App;

use Storage;

class CategoryFactory
{

    public function __construct()
    {

    }

    public function get_old_ids_array()
    {
        $checklist_ids = collect();
        $root_cats = Category::whereIsRoot()->whereNotIn('type',['system'])->get();
        foreach ($root_cats as $root) 
        {
            $checklist_ids = $checklist_ids->merge(Category::whereDescendantOrSelf($root)->where('old_id', '<>', 'null')->pluck('id'));
        }
        foreach ($checklist_ids as $id) 
        {
            $checklist_ids = $checklist_ids->merge(Category::whereAncestorOrSelf($id)->pluck('id'));
        }
        $checklist_ids = $checklist_ids->unique()->toArray();
        return $checklist_ids;
    }

    public function parse_taxonomy($saveToDatabase = true)
    {
        $content  = Storage::get('taxonomy.json');
        $json 	  = json_decode($content);
        $collect  = collect($json);
        $taxonomy = $collect->flatten(1)->toArray()[0]->body->outline[0]->outline;
        $tax_arr  = $this->recurse([], $taxonomy, $saveToDatabase);

        return $tax_arr;
    }

	public function recurse($arr, $cat, $save)
	{
	    // direct categories
	    if (isset($cat->outline))
	    {
			if (is_array($cat->outline))
			{
				foreach($cat->outline as $o)
		    	{
					if (isset($o->_text))
					{
						$lbl = $this->formatCategoryString($o->_text, $save);
						if (isset($o->outline))
						{
							$arr[$lbl] = $this->recurse([], $o, $save);
						}
						else
						{
							$arr = $this->addValue($arr, $o->_text, $save);
						}
					}
				}
			}
			else if (isset($cat->outline->_text))
			{
		    	$arr = $this->addValue($arr, $cat->outline->_text, $save);
			}
		}

		// sub categories
	    foreach($cat as $c)
	    {
			if (isset($c->outline))
			{
				$lbl1 = $this->formatCategoryString($c->_text, $save);
				$arr[$lbl1] = [];

				foreach ($c->outline as $t) 
		        {
		        	if (isset($t->_text))
		        	{
			        	$lbl2 = $this->formatCategoryString($t->_text, $save);

			        	if (isset($t->outline))
			        	{
				        	$arr[$lbl1][$lbl2] = $this->recurse([], $t, $save);
				        }
				        else
				        {
							$arr[$lbl1] = $this->addValue($arr[$lbl1], $t->_text, $save);
				        }
				    }
		        }
			}
	    }

	    return $arr;
	}

	public function addValue($arr, $txt, $save)
	{
		$lbl = $this->formatCategoryString($txt, $save);
		array_push($arr, $lbl);
		return $arr;
	}

	public function formatCategoryString($str, $save=false)
	{
		$out = str_replace(' / ', '_', $str);
		$out = str_replace(' ', '_', $out);
		$out = strtolower($out);
		$out = urlencode($out);

		if ($save)
			Translation::saveText('en_gb', $out, 'category', $str);

		return $out;
	}

	public function saveDynamicCategories($categories, $parent_id=null)
	{
	    if (is_array($categories))
        {
            foreach ($categories as $name => $category) 
        	{

	            if (is_array($category))
	            {
	            	$cat = new Category;
		            $cat->type  = 'list';
		            $cat->name  = $name;
		            $cat->parent_id = $parent_id;
		            $cat->category_input_id = $this->getCatrgoryInputId('list', 'list');
		            $cat->save();
	            	$this->saveDynamicCategories($category, $cat->id);
	            }
	            else
	            {
		            $cat 			  = new Category;
		            $cat->type 		  = $name;
		            $cat->name  	  = $category;
		            $cat->parent_id   = $parent_id;
		            $cat->category_input_id = $this->getCatrgoryInputId($name, 'list_item');
		            $cat->save();
	            }
	        }
        }
        else
        {
            $cat = new Category;
            $cat->type = 'list_item';
            $cat->name = $categories;
            $cat->parent_id = $parent_id;
            $cat->category_input_id = $this->getCatrgoryInputId($categories, 'list_item');
            $cat->save();
        }
	}

	public function getCatrgoryInputId($type, $fallbackType)
	{
		if (CategoryInput::where('type', $type)->count() > 0)
			return CategoryInput::where('type', $type)->value('id');
			
		return CategoryInput::where('type', $fallbackType)->value('id');
	}


	public function saveStaticCategories($categories)
	{
		foreach ($categories as $base_key => $base_cat) 
        {
            $base = new Category;
            $base->type  = $base_key;
            $base->name  = $base_key;
            $base->save();

            foreach ($base_cat as $cat_key => $cat_obj) 
            {
                
                if (is_array($cat_obj))
                {
                    $parent = new Category;
                    $parent->type  = 'category';
                    $parent->name  = $cat_key;
                    $parent->parent_id = $base->id;
                    $parent->save();

                    foreach ($cat_obj as $name => $type) 
                    {
                        
                        if (is_array($type))
                        {
                            $child = new Category;
                            $child->type  = 'select';
                            $child->name  = $name;
                            $child->parent_id = $parent->id;
                            $child->options = implode(',',$type);
                            $child->save();
                        }
                        else
                        {
                            $cat = new Category;
                            $cat->type = $type;
                            $cat->name = $name;
                            $cat->parent_id = $parent->id;
                            $cat->save();
                        }
                    }
                }
                else
                { 
                    $cat = new Category;
                    $cat->type  = 'category';
                    $cat->name  = $cat_obj;
                    $cat->parent_id = $base->id;
                    $cat->save();
                }
            }
        }

        Category::fixTree(); // kalnoy/nestedset: to fix the tree to fill _lft and _rgt columns
	}



	public function saveOldCategories()
	{
		$categories = [
            'action' =>[
                'food'=>[
                    'sugar_concentration_perc'=>'number',
                    'litres_of_suger_water'=>'number',
                ], 
                'disease'=>[
                    'varroa_treatment'=>['apistan','formic_acid','lactic_acid','oxalic_acid','powdered_sugar','other'],
                ],  
                'reminder'=>[
                    'reminder'=>'text',
                    'remind_date'=>'date',
                ],
                'colony'=>[
                    'split_colony'=>'boolean',
                    'merge_colonies'=>'boolean',
                ], 
                'size', 
                'queen'=>[
                    'queen_introduced'=>'text',
                    'queen_cells_removed'=>'number',
                    'queen_cells_left'=>'number',
                ], 
                'harvest'=>[
                    'kg_honey_harvested'=>'number',
                    'frames_with_honey_left'=>'number',
                ], 
                'tools', 
                'other'
            ],
            'condition' =>[
                'overall'=>[
                    'positive_impression'=>'score',
                    'needs_attention'=>'boolean',
                    'notes'=>'text',
                ], 
                'activity', 
                'brood', 
                'development'=>[
                    'frames_with_bees'=>'number',
                    'all_stages_of_brood_types'=>['eggs','larvae','pupae'],
                    'queen_present'=>'boolean',
                    'queen_cells'=>'number',
                    'queen_cells_types'=>['swarm','supercedure','emergency'],
                ], 
                'disease'=>[
                    'varroa_count'=>'number',
                ], 
                'flight', 
                'food'=>[
                    'sufficient_food'=>'score',
                    'frames_with_honey'=>'number',
                    'frames_with_pollen'=>'number',
                ], 
                'frame_stability', 
                'health',
                'loss'=>[
                    'colony_lost'=>'boolean',
                    'colony_lost_reason'=>'text',
                ], 
                'size', 
                'space', 
                'strength', 
                'temper', 
                'queen', 
                'winter_hardness', 
                'other'
            ],
            'hive_layer'=>['brood','honey','grid','empty'],
            'hive_frame'=>['wax','block','other'],
            'location'  =>['fixed','movable','temporary','other'],
            'production'=>['honey', 'wax', 'pollen', 'other'],
            'setting'   =>['app', 'general', 'sensor'],
            'sensor'    =>['beep','hiveeyes','arnia','manual','other'],
        ];  

        $this->saveStaticCategories($categories);
	}

}


