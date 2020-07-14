<?php

namespace App;

use Auth;

class HiveFactory
{

    public function __construct()
    {
        $this->layer_order = 0;
    }

	public function createHive($user_id, Location $location, $name, $hive_type_id, $color, $broodLayerAmount, $honeyLayerAmount, $frameAmount, $bb_width_cm, $bb_depth_cm, $bb_height_cm, $fr_width_cm, $fr_height_cm, $order)
	{
		$this->layer_order  = 0;

		$hive 	     		= new Hive();
		$hive->name  		= $name;
		$hive->order  		= $order;
		$hive->bb_width_cm  = $bb_width_cm;
		$hive->bb_depth_cm  = $bb_depth_cm;
		$hive->bb_height_cm = $bb_height_cm;
		$hive->fr_width_cm  = $fr_width_cm;
		$hive->fr_height_cm = $fr_height_cm;
		$hive->user_id 		= $user_id;
		$hive->color 		= $color;
		$hive->location_id  = $location->id;
		$hive->hive_type_id = $hive_type_id != '' && $hive_type_id != null ? $hive_type_id : 63;
		$hive->save();

		$layersBrood = $this->createLayers('brood', $broodLayerAmount, $color, $this->layer_order);
		$layersHoney = $this->createLayers('honey', $honeyLayerAmount, $color, $this->layer_order);
		$layers = $layersBrood->merge($layersHoney);

		$hive->layers()->saveMany($layers); 

		foreach ($layers as $layer) 
		{
			$layer->frames()->saveMany($this->createLayerFrames($frameAmount));
		}

		$location->hives()->save($hive);

		return $hive;
	}

	public function updateHive(Hive $hive, Location $location, $name, $hive_type_id, $color, $broodLayerAmount, $honeyLayerAmount, $frameAmount, $bb_width_cm, $bb_depth_cm, $bb_height_cm, $fr_width_cm, $fr_height_cm, $order)
	{
		
		$inspection_data 		  = [];
		$inspection_data['notes'] = __('beep.Hive').' auto '.strtolower(__('beep.Inspection'));
		$inspection_data['date']  = date('Y-m-d H:i');
		$inspection_data['items'] = [];
		if ($location->id != $hive->location_id)
		{
			$from_apiary_id = Category::findCategoryIdByRootParentAndName('hive', 'location', 'apiary', ['system','checklist']);
			if ($to_apiary_id)
				$inspection_data['items']["$from_apiary_id"] = $hive->location_id;

			$to_apiary_id   = Category::findCategoryIdByRootParentAndName('hive', 'relocation', 'destiny_apiary', ['system','checklist']);
			if ($to_apiary_id)
				$inspection_data['items']["$to_apiary_id"] = $location->id;
		}

		$hive->name  		= $name;
		$hive->order  		= $order;
		$hive->bb_width_cm  = $bb_width_cm;
		$hive->bb_depth_cm  = $bb_depth_cm;
		$hive->bb_height_cm = $bb_height_cm;
		$hive->fr_width_cm  = $fr_width_cm;
		$hive->fr_height_cm = $fr_height_cm;
		$hive->location_id  = $location->id;
		$hive->color 		= $color;
		$hive->hive_type_id = $hive_type_id;
		$hive->save();

		$layers 	 = collect();
		$layersBrood = collect();
		$layersHoney = collect();

		// get highest layer order
		$layer_order = -999999;
		foreach ($hive->layers as $l) 
			$layer_order = max($layer_order, $l->order);
		
		if ($layer_order == -999999)
			$layer_order = 0;

		// Create or delete layers
		$broodLayerDiff = $broodLayerAmount - $hive->getBroodlayersAttribute();
		if ($broodLayerDiff > 0)
		{
			$layersBrood = $this->createLayers('brood', $broodLayerDiff, $color, $layer_order+1);
			$layers->merge($layersBrood);
			$hive->layers()->saveMany($layersBrood);
		}
		else if ($broodLayerDiff < 0)
		{
			$category_id = Category::findCategoryIdByParentAndName('hive_layer', 'brood');
			$hive->layers()->where('category_id',$category_id)->limit(-1*$broodLayerDiff)->delete();
		}

		$honeyLayerDiff = $honeyLayerAmount - $hive->getHoneylayersAttribute();
		if ($honeyLayerDiff > 0)
		{
			$layersHoney = $this->createLayers('honey', $honeyLayerDiff, $color, $layer_order+1);
			$layers->merge($layersBrood);
			$hive->layers()->saveMany($layersHoney); 
		}
		else if ($honeyLayerDiff < 0)
		{
			$category_id = Category::findCategoryIdByParentAndName('hive_layer', 'honey');
			$hive->layers()->where('category_id',$category_id)->limit(-1*$honeyLayerDiff)->delete();
		}
		
		// Create new inspection 
		if ($broodLayerDiff != 0)
		{
			$added_brood_layers_id = Category::findCategoryIdByRootParentAndName('hive', 'configuration', 'brood_layers', ['system','checklist']);
			if ($added_brood_layers_id)
				$inspection_data['items'][$added_brood_layers_id] = $broodLayerDiff;

		}
		if ($honeyLayerDiff != 0)
		{
			$added_honey_layers_id = Category::findCategoryIdByRootParentAndName('hive', 'configuration', 'supers', ['system','checklist']);
			if ($added_honey_layers_id)
				$inspection_data['items'][$added_honey_layers_id] = $honeyLayerDiff;
		}

		if (($broodLayerDiff + $honeyLayerDiff) != 0)
		{
			$action_id = Category::findCategoryIdByRootParentAndName('hive', 'component', 'action', ['system','checklist']);
			if ($broodLayerDiff > 0)
				$action_item_id = Category::findCategoryIdByRootParentAndName('hive', 'action', 'added', ['system','checklist']);
			else if ($broodLayerDiff < 0)
				$action_item_id = Category::findCategoryIdByRootParentAndName('hive', 'action', 'removed', ['system','checklist']);

			if ($action_item_id)
				$inspection_data['items'][$action_id] = $action_item_id;
		}

		$inspection = Inspection::create($inspection_data);
		foreach ($inspection_data['items'] as $cat_id => $value) 
        {
            $itemData = 
            [
                'category_id'   => $cat_id,
                'inspection_id' => $inspection->id,
                'value'         => $value,
            ];
            InspectionItem::create($itemData);
        }

		$inspection->users()->sync(Auth::user()->id);

        if (isset($location))
            $inspection->locations()->sync($location->id);

        if (isset($hive))
            $inspection->hives()->sync($hive->id);


		// Adjust frames
		foreach ($hive->layers()->get() as $layer) 
		{
			$frameDiff = $frameAmount - $layer->frames()->count();
			// echo $frameAmount;
			// echo $layer->frames()->count();
			// echo $frameDiff;
			// die();
			if ($frameDiff > 0)
			{
				$layer->frames()->saveMany($this->createLayerFrames($frameDiff));
			}
			else if ($frameDiff < 0)
			{
				$category_id = Category::findCategoryIdByParentAndName('hive_frame', 'wax');
				$layer->frames()->where('category_id',$category_id)->limit(-1*$frameDiff)->delete();
			}
		}

		return $hive;
	}

	private function createLayers($type, $amount, $color, $order=0)
	{
		$layers = collect([]);
		for ($i=0; $i < $amount ;$i++) 
		{ 
			$layers->push($this->createLayer($type, $order, $color));
			$order++;
		}	

		return $layers;
	}

	private function createLayer($type, $order, $color)
	{
		$layer 				= new HiveLayer();
		$layer->order 		= $order;
		$layer->color 		= $color;
		$layer->category_id = Category::findCategoryIdByParentAndName('hive_layer', $type);
		return $layer;
	}

	private function createLayerFrames($amount)
	{
		$frames = collect([]);
		for ($i=0; $i < $amount ;$i++) 
		{ 
			$frames->push($this->createLayerFrame('wax', $i));
		}	

		return $frames;
	}

	private function createLayerFrame($type, $order)
	{
		$frame 				= new HiveLayerFrame();
		$frame->order 		= $order;
		$frame->category_id = Category::findCategoryIdByParentAndName('hive_frame', $type);
		return $frame;
	}

 	public function createMultipleHives($user_id, $amount, Location $location, $name, $hive_type_id, $color, $broodLayerAmount, $honeyLayerAmount, $frameAmount, $count_start, $bb_width_cm, $bb_depth_cm, $bb_height_cm, $fr_width_cm, $fr_height_cm)
	{
		$hives = collect([]);
		for ($i=0; $i < $amount ;$i++) 
		{ 
			$hives->push($this->createHive($user_id, $location, $name.' '.($count_start+$i), $hive_type_id, $color, $broodLayerAmount, $honeyLayerAmount, $frameAmount, $bb_width_cm, $bb_depth_cm, $bb_height_cm, $fr_width_cm, $fr_height_cm, null));
		}
		return $hives;
	}
}