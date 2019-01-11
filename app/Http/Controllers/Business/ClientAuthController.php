<?php
namespace App\Http\Controllers\Business;

use Auth;
use App\Billing\ClientAuthorization;
use App\Http\Requests\CreateClientAuthRequest;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Carbon\Carbon;

class ClientAuthController extends BaseController
{
    /**
     * Display a list of authorizations
     */
    public function listByClient($client_id)
    {
        $query = ClientAuthorization::where('client_id', $client_id)->ordered();
        $auths = $query->get();

        foreach($auths as $auth) {
            $auth->load('payer', 'service');
            $auth['effective_start'] = Carbon::parse($auth['effective_start'])->format('m/d/Y');
            $auth['effective_end'] = Carbon::parse($auth['effective_end'])->format('m/d/Y');
        }
        
        return $auths;
    }

    /**
     * Store a newly created authorization in storage.
     *
     * @param  \App\Http\Requests\CreateClientAuthRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateClientAuthRequest $request)
    {
        $data = $request->filtered();
        $this->authorize('create', [ClientAuthorization::class, $data]);

        if ($auth = ClientAuthorization::create($request->filtered())) {
            $auth['effective_start'] = Carbon::parse($auth['effective_start'])->format('m/d/Y');
            $auth['effective_end'] = Carbon::parse($auth['effective_end'])->format('m/d/Y');
            return new CreatedResponse('New authorization has been created', $auth->load('payer', 'service'));
        }
        
        return new ErrorResponse(500, 'The authorization could not be created.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\CreateClientAuthRequest  $request
     * @param  \App\Billing\ClientAuthorization  $auth
     * @return \Illuminate\Http\Response
     */
    public function update(CreateClientAuthRequest $request, ClientAuthorization $auth)
    {
        $this->authorize('update', $auth);

        if ($auth->update($request->filtered())) {
            $auth['effective_start'] = Carbon::parse($auth['effective_start'])->format('m/d/Y');
            $auth['effective_end'] = Carbon::parse($auth['effective_end'])->format('m/d/Y');
            return new SuccessResponse('Authorization has been updated.', $auth->load('payer', 'service'));
        }

        return new ErrorResponse(500, 'The authorization could not be updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Billing\ClientAuthorization  $auth
     * @return \Illuminate\Http\Response
     */
    public function destroy(ClientAuthorization $auth)
    {
        $this->authorize('delete', $auth);

        try {
            if ($auth->delete()) {
                return new SuccessResponse('Authorization has been deleted.');
            }
        } catch (\Exception $e) {
            logger($e->getMessage());
        }

        return new ErrorResponse(500, 'The authorization could not be deleted.');
    }
}
