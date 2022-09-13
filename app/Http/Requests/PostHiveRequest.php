<?php 

namespace App\Http\Requests;

use App\Http\Requests\Request;

class PostHiveRequest extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'name' 					=> 'required|string',
            'location_id' 			=> 'required|integer|exists:locations,id',
			'brood_layers'			=> 'nullable|required_without:layers|integer|min:0',
			'honey_layers'			=> 'nullable|required_without:layers|integer|min:0',
			'frames'				=> 'nullable|integer|min:0',
			'order' 				=> 'nullable|integer',
			'layers'				=> 'nullable|required_without_all:brood_layers,honey_layers|array',
			'color'					=> 'nullable|string|max:9', // #754B1F32 extra 2 characters are opacity
			'hive_type_id'			=> 'nullable|integer|exists:categories,id',
			'bb_width_cm'			=> 'nullable|numeric|min:0',
			'bb_depth_cm'			=> 'nullable|numeric|min:0',
			'bb_height_cm'			=> 'nullable|numeric|min:0',
			'fr_width_cm'			=> 'nullable|numeric|min:0',
			'fr_height_cm'			=> 'nullable|numeric|min:0',
			'queen.race_id'			=> 'nullable|integer|exists:categories,id',
			'queen.birth_date'		=> 'nullable|date',
			'queen.name'			=> 'nullable|string',
			'queen.description'		=> 'nullable|string',
			'queen.line'			=> 'nullable|string',
			'queen.tree'			=> 'nullable|string',
			'queen.color'			=> 'nullable|string|max:9', // #754B1F32 extra 2 characters are opacity
			'queen.clipped'			=> 'nullable|integer',
			'queen.fertilized'		=> 'nullable|integer',
			'timezone' 				=> 'nullable|timezone',
		];
	}

}
