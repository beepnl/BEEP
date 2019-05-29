<?php 

namespace App\Http\Requests;

use App\Http\Requests\Request;

class PostSensorRequest extends Request {

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
			'id'				=> 'nullable|integer|unique:sensors,id,'.$this->input('id'),
			'name' 				=> 'required|string',
            'hive_id' 			=> 'required|exists:hives,id',
			'type'				=> 'nullable|string|exists:categories,name',
			'key'				=> 'required|string|unique:sensors,key,'.$this->input('id'),
		];
	}

}
