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
			'hive_type_id'			=> 'nullable|integer|exists:categories,id',
			'queen.race_id'			=> 'nullable|integer|exists:categories,id',
			'queen.created_at'		=> 'nullable|date',
			'queen.name'			=> 'nullable|string',
		];
	}

}
