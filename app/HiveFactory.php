<?php

namespace App;

use Auth;
use Moment\Moment;

class HiveFactory
{

    public function __construct()
    {
        $this->layer_order = 0;
    }

	public function createHive($user_id, Location $location, $name, $hive_type_id, $color, $broodLayerAmount, $honeyLayerAmount, $frameAmount, $bb_width_cm, $bb_depth_cm, $bb_height_cm, $fr_width_cm, $fr_height_cm, $order, $hive_layers)
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

		$layers = collect();
		if (isset($hive_layers))
		{
			foreach ($hive_layers as $layer)
				$layers->add($this->createLayer($layer['type'], $layer['order'], $layer['color']));
		}
		else
		{
			$layersBrood = $this->createLayers('brood', $broodLayerAmount, $color, $this->layer_order);
			$layersHoney = $this->createLayers('honey', $honeyLayerAmount, $color, $this->layer_order);
			$layers = $layersBrood->merge($layersHoney);
		}

		$hive->layers()->saveMany($layers); 

		foreach ($layers as $layer) 
		{
			if ($layer->type == 'brood' || $layer->type == 'honey')
				$layer->frames()->saveMany($this->createLayerFrames($frameAmount));
		}

		$location->hives()->save($hive);

		return $hive;
	}

	public function updateHive(Hive $hive, Location $location, $name, $hive_type_id, $color, $broodLayerAmount, $honeyLayerAmount, $frameAmount, $bb_width_cm, $bb_depth_cm, $bb_height_cm, $fr_width_cm, $fr_height_cm, $order, $hive_layers, $timeZone)
	{
		
		// First set inspection because location will be fixed after setting in hive
		$inspection_items = [];
		$locationChange   = false;

		if ($location->id != $hive->location_id)
		{
			$locationChange = true;

			$from_apiary_id = Category::findCategoryIdByRootParentAndName('hive', 'relocation', 'previous_apiary', ['system','checklist']);
			if ($from_apiary_id)
				$inspection_items[$from_apiary_id] = $hive->getLocationAttribute();

			$to_apiary_id   = Category::findCategoryIdByRootParentAndName('hive', 'location', 'apiary', ['system','checklist']);
			if ($to_apiary_id)
				$inspection_items[$to_apiary_id] = $location->name;
		}

		// Edit hive
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

		$layers 			= collect();
		$broodLayerDiff 	= 0;
		$honeyLayerDiff 	= 0;
		$feedingBoxDiff		= 0;
		$queenExcluderDiff 	= 0;
		$frameDiff 			= 0; 

		$feedingBoxAmount 	= 0;
		$queenExcluderAmount= 0;

		if (isset($hive_layers)) // edit by layers object
		{

			$broodLayerAmount 	= 0;
			$honeyLayerAmount 	= 0;

			$broodLayerDiff   	= -1 * $hive->getBroodlayersAttribute();
			$honeyLayerDiff   	= -1 * $hive->getHoneylayersAttribute();
			$feedingBoxDiff   	= -1 * $hive->getFeedingBoxAttribute();
			$queenExcluderDiff	= -1 * $hive->getQueenExcluderAttribute();
			$foundLayerIds    	= [];
			
			foreach ($hive_layers as $layer)
			{
				$layer_type 	   = $layer['type'];
				$layer_type_cat_id = Category::findCategoryIdByRootParentAndName('hive', 'hive_layer', $layer_type);

				if ($layer_type_cat_id === null)
					$layer_type_cat_id = Category::findCategoryIdByRootParentAndName('hive', 'hive_layer', 'brood'); // set by deafult to brood

				if ($layer_type == 'brood')
				{
					$broodLayerDiff++;
					$broodLayerAmount++;
				}
				else if ($layer_type == 'honey')
				{
					$honeyLayerDiff++;
					$honeyLayerAmount++;
				}
				else if ($layer_type == 'feeding_box')
				{
					$feedingBoxDiff++;
					$feedingBoxAmount++;
				}
				else if ($layer_type == 'queen_excluder')
				{
					$queenExcluderDiff++;
					$queenExcluderAmount++;
				}

				if (!isset($layer['id'])) // create new layer
				{
					$new_layer = $hive->layers()->save($this->createLayer($layer_type, $layer['order'], $layer['color']));
					$foundLayerIds[] = $new_layer->id;
				}
				else // edit existing layer
				{
					$l = $hive->layers()->find($layer['id']);
					if ($l)
					{
						$foundLayerIds[] = $layer['id'];
						$l->category_id  = $layer_type_cat_id;
						$l->order 		 = $layer['order'];
						$l->color 		 = $layer['color'];
						$l->save();
					}
				}
			}
			// delete removed layers
			foreach ($hive->layers as $layer)
			{
				if (isset($layer['id']) && !in_array($layer['id'], $foundLayerIds))
					$hive->layers()->find($layer['id'])->delete();
			}
		}
		else // edit by $broodLayerAmount and $honeyLayerAmount (v2)
		{

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
				if ($category_id)
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
				if ($category_id)
					$hive->layers()->where('category_id',$category_id)->limit(-1*$honeyLayerDiff)->delete();
			}
		}
		
		
		// Adjust frames
		foreach ($hive->layers()->get() as $layer) 
		{
			if ($layer->type == 'brood' || $layer->type == 'honey'){
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
					if ($category_id)
						$layer->frames()->where('category_id',$category_id)->limit(-1*$frameDiff)->delete();
				}
			}
		}

		// Create auto inspection
		if ($broodLayerDiff != 0 || $honeyLayerDiff != 0 || $frameDiff != 0 || $feedingBoxDiff != 0 || $queenExcluderDiff != 0 || $locationChange)
		{
			// Inspection items to add 
			$brood_layers_id = Category::findCategoryIdByRootParentAndName('hive', 'configuration', 'brood_layers', ['system','checklist']);
			if ($brood_layers_id)
				$inspection_items[$brood_layers_id] = $broodLayerAmount;

			$honey_layers_id = Category::findCategoryIdByRootParentAndName('hive', 'configuration', 'supers', ['system','checklist']);
			if ($honey_layers_id)
				$inspection_items[$honey_layers_id] = $honeyLayerAmount;

			$frames_per_layer_id = Category::findCategoryIdByRootParentAndName('hive', 'configuration', 'frames_per_layer', ['system','checklist']);
			if ($frames_per_layer_id)
				$inspection_items[$frames_per_layer_id] = $frameAmount;

			$feeding_box_id = Category::findCategoryIdByRootParentAndName('hive', 'configuration', 'feeding_box', ['system','checklist']);
			if ($feeding_box_id)
				$inspection_items[$feeding_box_id] = $feedingBoxAmount;

			$queen_exluder_id = Category::findCategoryIdByRootParentAndName('hive', 'configuration', 'queen_excluder', ['system','checklist']);
			if ($queen_exluder_id)
				$inspection_items[$queen_exluder_id] = $queenExcluderAmount;

			$notes = Translation::translate('hive').' '.strtolower(Translation::translate('action'));
			Inspection::createInspection($inspection_items, $hive->id, $location->id, $notes, $timeZone);
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

 	public function createMultipleHives($user_id, $amount, Location $location, $name, $hive_type_id, $color, $broodLayerAmount, $honeyLayerAmount, $frameAmount, $count_start, $bb_width_cm, $bb_depth_cm, $bb_height_cm, $fr_width_cm, $fr_height_cm, $hive_layers)
	{
		$hives = collect([]);
		for ($i=0; $i < $amount ;$i++) 
		{ 
			$hives->push($this->createHive($user_id, $location, $name.' '.($count_start+$i), $hive_type_id, $color, $broodLayerAmount, $honeyLayerAmount, $frameAmount, $bb_width_cm, $bb_depth_cm, $bb_height_cm, $fr_width_cm, $fr_height_cm, null, $hive_layers));
		}
		return $hives;
	}
}