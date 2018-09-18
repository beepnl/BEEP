<?php 

namespace App\Http\Requests;

use App\Http\Requests\Request;

class PostCategoryRequest extends Request {

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
            'parent_id' 			=> 'required|exists:categories,id',
            'category_input_id' 	=> 'required|exists:category_inputs,id',
            'physical_quantity_id' 	=> 'present|required_with:physical_quantities,id',
			'type' 					=> 'string',
            'icon' 					=> 'image',
		];
	}

}
