<?php

namespace App;

use App\Checklist;
use App\Category;
use App\CategoryFactory;
use App\User;
use App\Inspection;
use App\InspectionItem;

class ChecklistFactory 
{

    public function __construct()
    {
        $this->categoryFactory       = new CategoryFactory;
        $this->std_checklist_type    = 'beep_v2';
        $this->std_checklist_name    = 'Beep v2';
        $this->old_inspection_items  = ['notes'=>27, 'attention'=>26, 'impression'=>25, 'reminder'=>8, 'reminder_date'=>9];
        $this->old_inspection_texts  = ['Varroatelling: '=>37, 'Resterende moerdoppen: '=>17];
        $this->old_list_category_ids = [6, 32, 35]; // lists
    }


    // Convert category_id's and tables from old database (v1: seeded categories) to new (v2: dynamic taxonomy categories)
    public function convertUsersChecklists($users, $debug=false)
    {
        // remove already existing copies of checklists
        Checklist::where('type', 'like', '%_copy%')->forceDelete();

        // perform magic
        $count = ['locations'=>0, 'inspections'=>0, 'conditions'=>0, 'actions'=>0, 'hives'=>0, 'hive_layers'=>0, 'hive_layer_frames'=>0, 'queens'=>0, 'sensors'=>0];

        $this->non_system_categories = Category::where('old_id','!=',null)->get();//getTaxonomy(null, false, true);
        print_r(['non_system_categories'=>$this->non_system_categories->pluck('old_id','id')->toArray()]);

        $stdChecklist          = $this->getStandardChecklist();
        $fallBackOfFallBacks   = Category::where('name','not_assigned_in_migration')->value('id',938); // id=938
        $fallback_loc_type_id  = $fallBackOfFallBacks;
        $fallback_hive_type_id = $fallBackOfFallBacks;
        $fallback_bee_race_id  = $fallBackOfFallBacks;
        $fallback_hive_layer_id= $fallBackOfFallBacks;
        $fallback_hive_frame_id= $fallBackOfFallBacks;
        $fallback_sensor_id    = $fallBackOfFallBacks;

        $loc_type_cats   = Category::descendentsByRootParentAndName('apiary', 'apiary', 'type');
        $hive_type_cats  = Category::descendentsByRootParentAndName('hive', 'hive', 'type');
        $bee_race_cats   = Category::descendentsByRootParentAndName('bee_colony', 'characteristics', 'subspecies');
        $hive_layer_cats = Category::descendentsByRootParentAndName('hive', 'app', 'hive_layer');
        $hive_frame_cats = Category::descendentsByRootParentAndName('hive', 'app', 'hive_frame');
        $hive_sensor_cats= Category::descendentsByRootParentAndName('hive', 'app', 'sensor');
        
        if ($loc_type_cats->where('name','fixed')->count() > 0)
            $fallback_loc_type_id = $loc_type_cats->where('name','fixed')->pluck('id')[0];

        if ($hive_type_cats->where('name','custom')->count() > 0)
            $fallback_hive_type_id = $hive_type_cats->where('name','custom')->pluck('id')[0];

        if ($bee_race_cats->where('name','other')->count() > 0)
            $fallback_bee_race_id = $bee_race_cats->where('name','other')->pluck('id')[0];

        if ($hive_layer_cats->where('name','brood')->count() > 0)
            $fallback_hive_layer_id = $hive_layer_cats->where('name','brood')->pluck('id')[0];

        if ($hive_frame_cats->where('name','wax')->count() > 0)
            $fallback_hive_frame_id = $hive_frame_cats->where('name','wax')->pluck('id')[0];

        if ($hive_sensor_cats->where('name','other')->count() > 0)
            $fallback_sensor_id = $hive_sensor_cats->where('name','other')->pluck('id')[0];

        print_r(['new_cat_ids'=> ['loc'=>$loc_type_cats->pluck('name','id')->toArray(), 'hive'=>$hive_type_cats->pluck('name','id')->toArray(), 'bee'=>$bee_race_cats->pluck('name','id')->toArray(), 'layer'=>$hive_layer_cats->pluck('name','id')->toArray(), 'frame'=>$hive_frame_cats->pluck('name','id')->toArray(), 'sensor'=>$hive_sensor_cats->pluck('name','id')->toArray()]]);
        print_r(['fallback_ids'=>['loc'=>$fallback_loc_type_id, 'hive'=>$fallback_hive_type_id, 'bee'=>$fallback_bee_race_id, 'layer'=>$fallback_hive_layer_id, 'frame'=>$fallback_hive_frame_id, 'sensor'=>$fallback_sensor_id]]);

        foreach ($users as $user) 
        {
            // create or get checklist
            if ($debug == false || $user->checklists()->count() == 0)
                $this->createUserChecklist($user, $stdChecklist);

            // 'location'  =>['fixed','movable','temporary','other'], (65,66,67,68)
            foreach ($user->locations as $location) 
                $count['locations'] += $this->changeCategoryIdToNew($loc_type_cats, $location, 'category_id', $fallback_loc_type_id);

            // 'sensor'    =>['beep','hiveeyes','arnia','manual','other']
            foreach ($user->sensors as $sensor) 
                $count['sensors'] += $this->changeCategoryIdToNew($hive_sensor_cats, $sensor, 'category_id', $fallback_sensor_id);


            // Change hives/inspections
            foreach ($user->hives()->withTrashed()->get() as $hive) 
            {
                $count['hives']  += $this->changeCategoryIdToNew($hive_type_cats, $hive, 'hive_type_id', $fallback_hive_type_id);
                if($hive->queen()->count() > 0)
                    $count['queens'] += $this->changeCategoryIdToNew($bee_race_cats, $hive->queen, 'race_id', $fallback_bee_race_id);

                // Transfer hive_layers, hive_layer_frame category_id's to taxonomy
                //  hive_layer.category_id, hive_layer_frame.category_id
                // 'hive_layer'=>['brood','honey','grid','empty'], (56,57,58,59)
                // 'hive_frame'=>['wax','block','other'], (61,62,63)
                foreach ($hive->layers()->withTrashed()->get() as $hive_layer) 
                {
                    $count['hive_layers'] += $this->changeCategoryIdToNew($hive_layer_cats, $hive_layer, 'category_id', $fallback_hive_layer_id);

                    foreach ($hive_layer->frames()->withTrashed()->get() as $layer_frame) 
                        $count['hive_layer_frames'] += $this->changeCategoryIdToNew($hive_frame_cats, $layer_frame, 'category_id', $fallback_hive_frame_id);

                }

                //die(print_r($hive->actions()->pluck('created_at')->toArray()));
                //die(print_r(['user'=>$user->name, 'count'=>$count]));
            }
            print_r(['id'=>$user->id, 'user'=>$user->name, 'count'=>$count]);
        }
        print_r(['total_count'=>$count]);

        if ($debug)
            die('End of debug output');
    }

    public function createUserChecklist($user, $checklist)
    {
        $hasNewChecklist = $user->checklists()->where('type',$checklist->type.'_copy')->count() > 0 ? true : false;
        
        if ($hasNewChecklist == false)
        {
            $new = ['type'=>$checklist->type.'_copy', 'name'=>$checklist->name.' - '.$user->name];
            $chk = $user->checklists()->create($new);
            $chk->syncCategories($checklist->category_ids);
        }
    }


    public function getStandardChecklist()
    {
        $query       = Checklist::where('type', $this->std_checklist_type)->orderBy('id', 'desc');
        $check_model = [ 'name' => $this->std_checklist_name, 'type' => $this->std_checklist_type, 'description' => 'Beep v1 kastkaart / hive checklist'];
        $check       = null;

        if ($query->count() == 0)
        {
            $check = new Checklist($check_model);
            $check->save();
            // create standard checklist
            $checklist_ids = $this->categoryFactory->get_old_ids_array();
            //die(print_r($checklist_ids));
            $check->categories()->sync($checklist_ids);
        }
        else
        {
            $check = $query->first();
        }
        //print_r(['getStandardChecklist'=>$check->toArray()]);
       
        return $check;
    }

    // $a => action/condition: {'hive_id', 'category_id', 'text', 'number', 'score', 'boolean', 'date'}
    private function createInspectionItem($a, $ins, $debug)
    {
        $cat_id       = null;
        $cat_type     = null;
        $ins_i        = null;
        $ins_id       = $ins->id;
        $old_id       = $a->category_id;
        $found        = $this->non_system_categories->where('old_id',$old_id)->where('type', '!=', 'system')->count();
        $is_core_item = array_search($old_id, $this->old_inspection_items);
        
        if ($is_core_item !== false)
        {
            if ($debug)
                echo("INS $ins->id: Found inspection core item $is_core_item \r\n");   
        }
        else if ($found == 0)
        {
            //print_r($this->non_system_categories->sortBy('old_id')->pluck('old_id','name')->toArray());
            echo("INS $ins->id: $old_id not found in ".$this->non_system_categories->count()." non_system_categories\r\n");
            //print_r($a->toArray());
            //die(print_r($this->non_system_categories->toArray()));
            return 0;
        }
        else if ($found == 1)
        {
            $cat        = $this->non_system_categories->where('old_id',$old_id)->first();
            $cat_id     = $cat->id;
            $cat_type   = $cat->input;
            if ($debug)
                echo("INS $ins->id: Found 1 cat with $old_id: $cat_id\r\n");
        }
        else if ($found > 1) // boolean + value item
        {
            if ($debug)
                echo("INS $ins->id: Found $found cat with $old_id \r\n");
            
            foreach ($this->non_system_categories->where('old_id',$old_id) as $cat) 
            {
                if ($cat->isSystem() == false)
                {
                    $cat_type = $cat->input;

                    if($cat_type == 'boolean' || $cat_type == 'boolean_yes_red')
                    {
                        $data  = ['value'=>'1', 'inspection_id'=>$ins_id, 'category_id'=>$cat->id];
                        $ins_i = InspectionItem::create($data);
                        if ($debug)
                            echo("INS $ins->id: $cat_type created cat $cat->id\r\n");
                    }
                    else
                    {
                        $cat_id = $cat->id;
                    }
                }
            }
        }
        
        $value = null;


        
        if (isset($a) && gettype($a) == 'object')
        {
            if ($a->text !== null)
                $value = substr($a->text, 0, 1024);
            else if ($a->number !== null)
                $value = "$a->number";
            else if($a->score !== null)
                $value = "$a->score";
            else if($a->boolean !== null)
                $value = "$a->boolean";
            else if($a->date !== null)
                $value = "$a->date";
        }

        if ($value === null || $value === 'null' || $value === '')
        {
            if ($debug == false)
                $a->delete();

            return 0;
        }

        // if ($old_id == 33 && $a->boolean === 0)
        //     die(print_r(['a'=>$a,'cat_id'=>$cat_id, 'value'=>$value]));

        // Items that have to be processed before new InspectionItem creation
        // Save core item, or gather list items
        if ($is_core_item !== false) 
        {
            $ins->$is_core_item = $value;
            $ins->save();
            $value = null; // don't create an inspection item, because a core item is already created
            $ins_i = true; // delete former item
        }
        else if (in_array($old_id, $this->old_list_category_ids)) // boolean list
        {
            $values = explode(',',$value);
            //print_r(["old_ids options (list=$old_id)"=>$values]);
            $cat_ids= [];
            foreach ($values as $val) 
            {
                $cat_opt = $this->non_system_categories->where('old_id',$val)->first(); // check if there is a list option with the name (or id) of the old_id
                if ($cat_opt)
                {
                    $cat_opt_id = $cat_opt->id;
                    if ($cat_opt->input == 'boolean' || $cat_opt->input == 'boolean_yes_red')
                    {
                        $data  = ['value'=>'1', 'inspection_id'=>$ins_id, 'category_id'=>$cat_opt_id];
                        $ins_i = InspectionItem::create($data);
                        if ($debug)
                            echo("INS $ins->id: boolean_list_item_created cat $cat_opt_id from old_id $val\r\n");
                    }
                    else if ($cat_opt_id) // list items
                    {
                        array_push($cat_ids, $cat_opt_id);
                    }
                }

            }
            $value = implode(',', $cat_ids);
        }
                 
        // Save to new inspection item
        if ($value !== null && $value !== '' && $cat_id !== null)
        {
            if ($cat_type == 'text')
            {
                $preFixText = array_search($old_id, $this->old_inspection_texts);
                if ($preFixText !== false)
                {
                   $value = $preFixText.$value;
                }
                else if ($debug)
                {
                    echo("INS $ins->id: No prefix Text item for old_id=$old_id ".Category::where('id',$cat_id)->first()->ancName()."\r\n");
                }
            }
            
            $data  = ['value'=>"$value", 'inspection_id'=>$ins_id, 'category_id'=>$cat_id];
            $ins_i = InspectionItem::create($data);

            if ($debug && $ins_i)
                echo("INS $ins->id: InspectionItem created type: $cat_type, cat: $cat_id, value: $value\r\n");

            // if ($debug)
            // {
            //     if ($cat_id == 804) // 804 => food > feeding > concentration
            //         print_r(['cat_id'=>$cat_id, 'ins_i'=>$ins_i]);
            // }
        }
        
        if ($ins_i)
        {
            if ($debug == false)
                $a->delete();

            return 1;
        }
        return 0;
    }

    private function changeCategoryIdToNew($categories, $item, $cat_field='category_id', $fallback_cat_id=null)
    {
        if (gettype($item) == 'object')
        {
            $new_cat_id = null;
            $new_cat    = $categories->where('old_id', $item->$cat_field);

            if ($new_cat->count() > 0)
            {
                $new_cat_id = $new_cat->pluck('id')[0];
            }
            else
            { 
                if ($categories->where('id', $item->$cat_field)->count() == 0) // if new category id is already assigned, ignore
                    $new_cat_id = $fallback_cat_id;
            }
            //print_r(['item'=>$item->name, "$cat_field"=>$item->$cat_field, 'new_cat_id'=>$new_cat_id, 'fallback_cat_id'=>$fallback_cat_id]);

            if ($new_cat_id && $item->$cat_field != $new_cat_id)
            {
                $item->$cat_field = $new_cat_id;
                return $item->save();
            }
        }
        else
        {
            print_r(['changeCategoryIdToNew unknown item'=>$item]);
        }
        return 0;
    }

}
