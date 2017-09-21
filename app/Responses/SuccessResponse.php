<?php

namespace App\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

class SuccessResponse implements Responsable
{
    protected $statusCode = 200;
    protected $message;
    protected $data;

    public function __construct($message, $data = [])
    {
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        $response['message'] = $this->message;
        if (count($this->data)) $response['data'] = $this->data;
        return new JsonResponse($response, $this->statusCode);
    }
}
