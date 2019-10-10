<?php

namespace App\Exceptions;

use Illuminate\Contracts\Support\Responsable;
use RuntimeException;

class HttpResponsableException extends RuntimeException
{
    /**
     * The underlying response instance.
     *
     * @var Responsable
     */
    protected $response;

    /**
     * Create a new HTTP response exception instance.
     *
     * @param Responsable $response
     */
    public function __construct(Responsable $response)
    {
        $this->response = $response;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request
     * @return Responsable
     */
    public function render($request)
    {
        return $this->response;
    }
}
