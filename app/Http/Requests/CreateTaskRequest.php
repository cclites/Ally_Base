<?php
namespace App\Http\Requests;

use Carbon\Carbon;

class CreateTaskRequest extends BusinessRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:65535',
            'due_date' => 'nullable|date',
            'assigned_user_id' => 'nullable|exists:users,id',
            'completed' => 'nullable|boolean',
        ];
    }

    public function filtered()
    {
        $data = $this->validated();
        $data['due_date'] = isset($data['due_date']) ? Carbon::parse($data['due_date']) : null;

        return $data;
    }
}
