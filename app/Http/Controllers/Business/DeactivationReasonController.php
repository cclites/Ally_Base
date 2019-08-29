<?php

namespace App\Http\Controllers\Business;

use App\DeactivationReason;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use App\User;

class DeactivationReasonController extends BaseController
{
    /**
     * Get a list of the current business chain's deactivation reasons.
     *
     * @return \Illuminate\Htt\Response
     * @throws \Exception
     */
    public function index()
    {
        $chain = $this->businessChain();

        return response()->json([
            'client' => $chain->clientDeactivationReasons,
            'caregiver' => $chain->caregiverDeactivationReasons,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'string|required',
            'name' => 'string|required'
        ]);

        $this->authorize('create', [DeactivationReason::class, $data]);

        if ($reason = $this->businessChain()->deactivationReasons()->create($data)) {
            return new SuccessResponse(ucfirst($data['type']) . ' Deactivation reason created.', $reason);
        }

        return new ErrorResponse(500, 'An unexpected error occurred.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DeactivationReason  $reason
     * @return \Illuminate\Http\Response
     * @throws AuthorizationException
     */
    public function destroy(DeactivationReason $reason)
    {
        if (empty($reason->chain_id)) {
            return new ErrorResponse(403, "You cannot remove factory deactivation reason codes.");
        }

        $this->authorize('delete', $reason);

        if (User::where('deactivation_reason_id', $reason->id)->exists()) {
            return new ErrorResponse(403, "Could not remove the deactivation reason \"{$reason->name}\" because it is currently in use in the system.");
        }

        try {
            $reason->delete();
        } catch (\Exception $ex) {
            return new ErrorResponse(500, 'An unexpected error occurred.');
        }

        return new SuccessResponse("Deactivation reason \"{$reason->name}\" successfully removed.", $reason);
    }
}
