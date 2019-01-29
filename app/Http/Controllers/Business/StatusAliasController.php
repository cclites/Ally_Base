<?php

namespace App\Http\Controllers\Business;

use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\StatusAlias;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use App\Business;
use Illuminate\Validation\Rule;
use App\User;

class StatusAliasController extends BaseController
{
    /**
     * Get a list of the status aliases available for 
     * the current Business.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(
            $this->business()->statusAliases->groupBy('type')
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * @throws AuthorizationException
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'business_id' => 'required',
            'name' => 'required|max:255|unique:status_aliases,name',
            'active' => 'required|boolean',
            'type' => 'required|in:client,caregiver',
        ]);

        $business = Business::findOrFail($request->business_id);
        $this->authorize('update', $business);

        $status = StatusAlias::create($data);

        return new SuccessResponse('Status alias saved.', $status);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\StatusAlias  $statusAlias
     * @return \Illuminate\Http\Response
     * @throws AuthorizationException
     */
    public function update(Request $request, StatusAlias $statusAlias)
    {
        $this->authorize('update', $statusAlias->business);

        $data = $request->validate([
            'business_id' => 'required',
            'name' => ['required', 'max:255', Rule::unique('status_aliases')->ignore($statusAlias->id)],
            'active' => 'required|boolean',
            'type' => 'required|in:client,caregiver',
        ]);

        if ($statusAlias->update($data)) {
            return new SuccessResponse('Status alias updated.', $statusAlias);
        }

        return new ErrorResponse(500, 'An unexpected error occurred.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\StatusAlias  $statusAlias
     * @return \Illuminate\Http\Response
     */
    public function destroy(StatusAlias $statusAlias)
    {
        $this->authorize('update', $statusAlias->business);

        if (User::where('status_alias_id', $statusAlias->id)->exists()) {
            return new ErrorResponse(403, 'Unable to remove status alias because it is already in use.');
        }

        if ($statusAlias->delete()) {
            return new SuccessResponse('Status alias removed.', $statusAlias);
        }

        return new ErrorResponse(500, 'An unexpected error occurred.');
    }
}
