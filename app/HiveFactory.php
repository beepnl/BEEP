<?php

namespace App;

class HiveFactory
{

    public function __construct()
    {
        $this->layer_order = 0;
    }

	public function createHive($user_id, Location $location, $name, $hive_type_id, $color, $broodLayerAmount, $honeyLayerAmount, $frameAmount)
	{
		$this->layer_order  = 0;

		$hive 	     		= new Hive();
		$hive->name  		= $name;
		$hive->user_id 		= $user_id;
		$hive->color 		= $color;
		$hive->location_id  = $location->id;
		$hive->hive_type_id = $hive_type_id;
		$hive->save();

		$layersBrood = $this->createLayers('brood', $broodLayerAmount, $color);
		$layersHoney = $this->createLayers('honey', $honeyLayerAmount, $color);
		$layers = $layersBrood->merge($layersHoney);

		$hive->layers()->saveMany($layers); 

		foreach ($layers as $layer) 
		{
			$layer->frames()->saveMany($this->createLayerFrames($frameAmount));
		}

		$location->hives()->save($hive);

		return $hive;
	}

	public function updateHive(Hive $hive, Location $location, $name, $hive_type_id, $color, $broodLayerAmount, $honeyLayerAmount, $frameAmount)
	{
		$hive->name  		= $name;
		$hive->location_id  = $location->id;
		$hive->color 		= $color;
		$hive->hive_type_id = $hive_type_id;
		$hive->save();

		$layers = collect();
		$broodLayerDiff = $broodLayerAmount - $hive->getBroodlayersAttribute();
		if ($broodLayerDiff > 0)
		{
			$layersBrood = $this->createLayers('brood', $broodLayerDiff, $color);
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
			$layersHoney = $this->createLayers('honey', $honeyLayerDiff, $color);
			$layers->merge($layersBrood);
			$hive->layers()->saveMany($layersHoney); 
		}
		else if ($honeyLayerDiff < 0)
		{
			$category_id = Category::findCategoryIdByParentAndName('hive_layer', 'honey');
			$hive->layers()->where('category_id',$category_id)->limit(-1*$honeyLayerDiff)->delete();
		}
		
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

	private function createLayers($type, $amount, $color)
	{
		$layers = collect([]);
		for ($i=0; $i < $amount ;$i++) 
		{ 
			$layers->push($this->createLayer($type, $this->layer_order, $color));
			$this->layer_order++;
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

	public function createMultipleHives($user_id, $amount, Location $location, $name, $hive_type_id, $color, $broodLayerAmount, $honeyLayerAmount, $frameAmount, $count_start)
	{
		$hives = collect([]);
		for ($i=0; $i < $amount ;$i++) 
		{ 
			$hives->push($this->createHive($user_id, $location, $name.' '.($count_start+$i), $hive_type_id, $color, $broodLayerAmount, $honeyLayerAmount, $frameAmount));
		}
		return $hives;
	}
}