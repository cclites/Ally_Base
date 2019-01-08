<?php
namespace App\Http\Requests;

class CreateServiceRequest extends BusinessRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:100',
            'default' => 'nullable|boolean',
            'business_id' => 'nullable|integer'
        ];
    }

    public function filtered() {
        $data = $this->validated();
        $data['chain_id'] = auth()->user()->officeUser->chain_id;
        return $data;
    }

}
