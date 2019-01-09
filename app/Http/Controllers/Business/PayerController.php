<?php

namespace App\Http\Controllers\Business;

use App\Billing\Payer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Responses\SuccessResponse;
use App\Http\Requests\CreatePayerRequest;
use App\Http\Requests\UpdatePayerRequest;
use App\Responses\ErrorResponse;

class PayerController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $payers = $this->businessChain()->payers()->ordered()->get();

        if ($request->wantsJson() && $request->json) {
            return response()->json($payers);
        }

        return view('business.payers', compact('payers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CreatePayerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreatePayerRequest $request)
    {
        $data = array_merge($request->validated(), ['chain_id' => $this->businessChain()->id]);

        $this->authorize('create', [Payer::class, $data]);

        if ($payer = Payer::create($data)) {
            return new SuccessResponse('Payer added successfully.', $payer);
        }

        return new ErrorResponse(500, 'An unexpected error occurred.  Please try again.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Payer  $payer
     * @return \Illuminate\Http\Response
     */
    public function show(Payer $payer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdatePayerRequest  $request
     * @param  \App\Payer  $payer
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePayerRequest $request, Payer $payer)
    {
        $data = array_merge($request->validated(), ['chain_id' => $this->businessChain()->id]);

        $this->authorize('update', $payer);

        if ($payer->update($data)) {
            return new SuccessResponse('Payer details updated successfully.', $payer);
        }

        return new ErrorResponse(500, 'An unexpected error occurred.  Please try again.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Payer  $payer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Payer $payer)
    {
        $this->authorize('delete', $payer);

        if ($payer->delete()) {
            return new SuccessResponse('Payer deleted successfully.', $payer);
        }

        return new ErrorResponse(500, 'An unexpected error occurred.  Please try again.');
    }
}
