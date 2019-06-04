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
use App\Billing\Validators\PayerRateValidator;

class PayerController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $payers = $this->businessChain()->payers()
            ->with(['rates', 'rates.service'])
            ->ordered()
            ->get();

        if ($request->wantsJson() && $request->json) {
            return response()->json($payers);
        }

        $services = $this->businessChain()->services()
            ->ordered()
            ->get();
            
        return view('business.payers', compact(['payers', 'services']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CreatePayerRequest  $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function store(CreatePayerRequest $request)
    {
        $data = $request->filtered();

        $this->authorize('create', [Payer::class, $data]);

        \DB::beginTransaction();
        try {
            if (! $payer = Payer::create($data)) {
                throw new \Exception();
            }

            // Force payers to provider pay for now
            $payer->setProviderPay();
    
            if (! $payer->syncRates($data['rates'] ?? [])) {
                throw new \Exception();
            }
            
            $validator = new PayerRateValidator();
            if (! $validator->validate($payer->fresh())) {
                return new ErrorResponse(422, $validator->getErrorMessage());
            }

            \DB::commit();
            return new SuccessResponse('Payer added successfully.', $payer->fresh());
        } catch (\Exception $ex) {
            \DB::rollBack();
            return new ErrorResponse(500, 'An unexpected error occurred.  Please try again.');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdatePayerRequest  $request
     * @param  \App\Billing\Payer  $payer
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function update(UpdatePayerRequest $request, Payer $payer)
    {
        $this->authorize('update', $payer);

        $data = $request->filtered();

        \DB::beginTransaction();
        try {
            if (! $payer->update($data)) {
                throw new \Exception();
            }
    
            if (! $payer->syncRates($data['rates'] ?? [])) {
                throw new \Exception();
            }
            
            $validator = new PayerRateValidator();
            if (! $validator->validate($payer->fresh())) {
                \DB::rollBack();
                return new ErrorResponse(422, $validator->getErrorMessage());
            }

            \DB::commit();
            return new SuccessResponse('Payer details updated successfully.', $payer->fresh());
        } catch (\Exception $ex) {
            \Log::debug($ex->getMessage());
            \DB::rollBack();
            return new ErrorResponse(500, 'An unexpected error occurred.  Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Billing\Payer  $payer
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Payer $payer)
    {
        $this->authorize('delete', $payer);

        try {
            if ($payer->delete()) {
                return new SuccessResponse('Payer deleted successfully.', $payer);
            }
        } catch (\Exception $ex) {
            logger($ex->getMessage());
        }

        return new ErrorResponse(500, 'Payer could not be deleted.');
    }
}
