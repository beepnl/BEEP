<?php

use Illuminate\Database\Seeder;
use App\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     *
     * Build category tree to refer to in actions, conditions, etc.
     * type that is boolean, text, number refers to type to store data in in refence table (actions, conditions, etc)
     */
    public function run()
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
        
        foreach ($categories as $base_key => $base_cat) 
        {
            $base = new Category;
            $base->type  = 'base_category';
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
    }
}
