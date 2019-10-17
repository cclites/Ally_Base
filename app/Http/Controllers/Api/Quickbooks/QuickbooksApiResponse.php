<?php

namespace App\Http\Controllers\Api\Quickbooks;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SuccessResponse
 * @package App\Responses
 * @mixin \Illuminate\Http\Response
 */
class QuickbooksApiResponse implements Responsable
{
    /**
     * @var int
     */
    protected $statusCode = 200;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var array|null
     */
    protected $data;

    /**
     * QuickbooksApiResponse constructor.
     *
     * @param string $message
     * @param array|null $data
     * @param int $status
     */
    public function __construct(string $message, ?array $data = [], int $status = 200)
    {
        $this->statusCode = $status;
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return JsonResponse
     */
    public function toResponse($request) : Response
    {
        $response = [
            'status' => $this->statusCode,
            'message' => $this->message,
            'data' => $this->data,
        ];

        return new JsonResponse($response, 200);
    }
}
