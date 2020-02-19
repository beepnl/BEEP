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
			'name' 					=> 'nullable|required_without:names|string',
			'names' 				=> 'nullable|required_without:name|string',
            'category_input_id' 	=> 'required|exists:category_inputs,id',
            'physical_quantity_id' 	=> 'present|required_with:physical_quantities,id',
            'parent_id' 			=> 'nullable|exists:categories,id',
			'type' 					=> 'string',
            'icon' 					=> 'image',
		];
	}

}
