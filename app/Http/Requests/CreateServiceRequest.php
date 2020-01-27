<?php
namespace App\Http\Requests;

use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreateServiceRequest extends FormRequest
{
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
            'name' => 'required|string|max:70',
            'code' => 'nullable|string|max:10',
            'mod1' => 'nullable|string|max:10',
            'mod2' => 'nullable|string|max:10',
            'default' => 'nullable|boolean',
        ];
    }

    public function filtered() {
        $data = $this->validated();
        $data['chain_id'] = Auth::user()->officeUser->chain_id;
        return $data;
    }

}
