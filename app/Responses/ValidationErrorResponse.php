<?php
namespace App\Responses;

use Illuminate\Contracts\Support\Responsable;

/**
 * Class ValidationErrorResponse
 * @package App\Responses
 *
 * This class is used to simulate a validation error to return the same response as a $request->validate failure
 */
class ValidationErrorResponse implements Responsable
{
    protected $field;
    protected $message;

    public function __construct($field, $message)
    {
        $this->field = $field;
        $this->message = $message;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        request()->validate(
            [$this->field => 'nullable|integer|min:99999999928381|max:99999999928381'], // some fake validation that will always fail
            [$this->field . '.*' => $this->message]
        );

        // shouldn't get to this point
        return response()->json([], 422);
    }
}