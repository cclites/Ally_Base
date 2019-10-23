<?php

namespace App\Http\Controllers\Business;

use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\StatusAlias;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use App\Business;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use App\User;

class StatusAliasController extends BaseController
{
    /**
     * Helper function to get all status aliases.
     *
     * @return Collection
     */
    public function getStatusAliases() : Collection
    {
        return $this->businessChain()->statusAliases()
            ->get()
            ->groupby('type')
            ->sortBy('name');
    }

    /**
     * Get a list of the status aliases available for 
     * the current BusinessChain.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if( request()->input( 'business_id', null ) ){

            $chain = Business::find( request()->input( 'business_id' ) )->chain;
            $this->setBusinessChainAs( $chain );
        }

        return response()->json(
            $this->getStatusAliases()
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
        $unique = Rule::unique('status_aliases', 'name')
            ->where('chain_id', auth()->user()->getChain()->id);

        if ($request->type) {
            $unique->where('type', $request->type);
        }

        $data = $request->validate([
            'name' => [
                'required',
                'max:255',
                $unique
            ],
            'active' => 'required|boolean',
            'type' => 'required|in:client,caregiver',
        ]);

        $this->authorize('update', $this->businessChain());

        $this->businessChain()->statusAliases()->create($data);

        return new SuccessResponse('Status alias saved.', $this->getStatusAliases());
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
        $this->authorize('update', $this->businessChain());

        $data = $request->validate([
            'name' => [
                'required',
                'max:255',
                Rule::unique('status_aliases', 'name')
                    ->where('chain_id', auth()->user()->getChain()->id)
                    ->where('type', $request->type)
                    ->ignore($statusAlias->id)
            ],
            'active' => 'required|boolean',
            'type' => 'required|in:client,caregiver',
        ]);

        // cannot allow changing of the 'active' type once a status alias is in use
        // because it will cause issues with how users are filtered.
        if ($data['active'] != $statusAlias->active && User::where('status_alias_id', $statusAlias->id)->exists()) {
            return new ErrorResponse(403, 'Cannot change active value for this status alias because it is currently in use.');
        }

        if ($statusAlias->update($data)) {
            return new SuccessResponse('Status alias updated.', $this->getStatusAliases());
        }

        return new ErrorResponse(500, 'An unexpected error occurred.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\StatusAlias  $statusAlias
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(StatusAlias $statusAlias)
    {
        $this->authorize('update', $this->businessChain());

        if (User::where('status_alias_id', $statusAlias->id)->exists()) {
            return new ErrorResponse(403, 'Unable to remove status alias because it is already in use.');
        }

        if ($statusAlias->delete()) {
            return new SuccessResponse('Status alias removed.', $this->getStatusAliases());
        }

        return new ErrorResponse(500, 'An unexpected error occurred.');
    }
}
