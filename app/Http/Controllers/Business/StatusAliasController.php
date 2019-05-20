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
     * the current BusinessChain.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return response()->json(
            $this->businessChain()->statusAliases->groupby('type')
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
            'name' => [
                'required',
                'max:255',
                Rule::unique('status_aliases', 'name')
                    ->where('chain_id', auth()->user()->getChain()->id)
            ],
            'active' => 'required|boolean',
            'type' => 'required|in:client,caregiver',
        ]);

        $this->authorize('update', $this->business());

        $status = $this->businessChain()->statusAliases()->create($data);

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
        $this->authorize('update', $this->business());

        $data = $request->validate([
            'name' => [
                'required',
                'max:255',
                Rule::unique('status_aliases', 'name')
                    ->where('chain_id', auth()->user()->getChain()->id)
                    ->ignore($statusAlias->id)
            ],
            'active' => 'required|boolean',
            'type' => 'required|in:client,caregiver',
        ]);

        // cannot allow chaging of the 'active' type once a status alias is in use
        // because it will cause issues with how users are filtered.
        if ($data['active'] != $statusAlias->active && User::where('status_alias_id', $statusAlias->id)->exists()) {
            return new ErrorResponse(403, 'Cannot change active value for this status alias because it is currently in use.');
        }

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
        $this->authorize('update', $this->business());

        if (User::where('status_alias_id', $statusAlias->id)->exists()) {
            return new ErrorResponse(403, 'Unable to remove status alias because it is already in use.');
        }

        if ($statusAlias->delete()) {
            return new SuccessResponse('Status alias removed.', $statusAlias);
        }

        return new ErrorResponse(500, 'An unexpected error occurred.');
    }
}
