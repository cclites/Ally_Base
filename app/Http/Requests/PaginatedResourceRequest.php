<?php

namespace App\Http\Requests;

use App\Billing\ClaimPayment;
use App\Rules\ValidEnum;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use App\Billing\Payments\PaymentDescriptionTypes;

class PaginatedResourceRequest extends FormRequest
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
            'per_page' => 'nullable|numeric',
            'page' => 'nullable|numeric',
            'sort' => 'nullable|string',
            'desc' => 'nullable|boolean',
        ];
    }

    /**
     * Get the filtered request data.
     *
     * @return array
     */
    public function filtered()
    {
        $data = $this->validated();
        return $data;
    }

    public function getPerPage(int $default = 25) : int
    {
        return $this->per_page ?: $default;
    }

    public function getPage() : int
    {
        return $this->page ?: 1;
    }

    public function getOffset() : int
    {
        return ($this->getPage() - 1) * $this->getPerPage();
    }

    public function getSort(string $default = null) : string
    {
        return $this->sort ?: $default;
    }

    public function getSortOrder() : string
    {
        return $this->desc == 'true' ? 'desc' : 'asc';
    }
}