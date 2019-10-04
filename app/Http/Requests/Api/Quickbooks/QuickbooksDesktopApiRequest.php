<?php

namespace App\Http\Requests\Api\Quickbooks;

use App\QuickbooksConnection;
use App\Business;
use App\Responses\ErrorResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class QuickbooksDesktopApiRequest extends FormRequest
{
    /**
     * @var \App\Business
     */
    public $business;

    /**
     * @var \App\QuickbooksConnection
     */
    public $connection;

    /**
     * A default authorize() method to return true.  Authorization rules normally belong in a policy, called from the controller.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Invalid Request.',
            'errors' => $validator->errors(),
        ], 422));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'key' => 'required|exists:quickbooks_connections,desktop_api_key',
        ];
    }

    /**
     * Get the QuickbooksConnection based on the request API Key.
     *
     * @return QuickbooksConnection|null
     */
    public function connection(): ?QuickbooksConnection
    {
        if (isset($this->connection)) {
            return $this->connection;
        }

        $this->connection = QuickbooksConnection::with('business')
            ->where('is_desktop', true)
            ->where('desktop_api_key', $this->key)
            ->first();

        if (empty($this->connection)) {
            abort(404);
        }

        return $this->connection;
    }

    /**
     * Get the Business based on the request API Key.
     *
     * @return Business|null
     */
    public function business(): ?Business
    {
        if (isset($this->business)) {
            return $this->business;
        }

        $this->business = $this->connection()->business;

        if (empty($this->business)) {
            abort(404);
        }

        return $this->business;
    }
}
