<?php

namespace App\Http\Controllers\Business;

use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\SystemException;
use Illuminate\Http\Request;

class ExceptionController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = SystemException::forRequestedBusinesses()->ordered();
        $exceptions = (clone $query)->whereNull('acknowledged_at')
            ->get();

        if ($request->expectsJson() && $request->input('json')) {
            return collection_only_values($exceptions, ['id', 'title', 'description', 'created_at']);
        }

        $archived = (clone $query)->whereNotNull('acknowledged_at')
            ->orderBy('acknowledged_at', 'DESC')
            ->get();

        return view('business.exceptions.index', compact('exceptions', 'archived'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SystemException  $exception
     * @return \Illuminate\Http\Response
     */
    public function show(SystemException $exception)
    {
        $this->authorize('read', $exception);
        return view('business.exceptions.show', compact('exception'));
    }

    /**
     * Acknowledge the specific exception
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\SystemException $exception
     * @return \App\Responses\ErrorResponse|\App\Responses\SuccessResponse
     */
    public function acknowledge(Request $request, SystemException $exception)
    {
        $this->authorize('update', $exception);

        if ($exception->acknowledge($request->input('notes', ''))) {
            return new SuccessResponse('You have successfully acknowlegded the exception.', [], route('business.exceptions.index'));
        }

        return new ErrorResponse(500, 'Error updating exception.');
    }
}
