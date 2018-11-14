<?php

namespace App\Http\Controllers\Business;

use App\Http\Requests\UpdateRateCodeRequest;
use App\RateCode;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;

class RateCodeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $this->business()->rateCodes();

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $rateCodes = $query->orderBy('name')->get();

        if ($request->expectsJson()) {
            return $rateCodes;
        }

        return view('business.rate_codes.index', compact('rateCodes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UpdateRateCodeRequest $request)
    {
        $rateCode = $this->business()->rateCodes()->create($request->validated());
        return new CreatedResponse('The rate code has been created.', $rateCode);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\RateCode  $rateCode
     * @return \Illuminate\Http\Response
     */
    public function show(RateCode $rateCode)
    {
        if ($rateCode->business_id != $this->business()->id) {
            return new ErrorResponse(403, 'You do not have access to this rate code.');
        }

        return response($rateCode);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateRateCodeRequest $request
     * @param  \App\RateCode $rateCode
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRateCodeRequest $request, RateCode $rateCode)
    {
        if ($rateCode->business_id != $this->business()->id) {
            return new ErrorResponse(403, 'You do not have access to this rate code.');
        }

        $rateCode->update($request->validated());
        return new SuccessResponse('The rate code has been updated', $rateCode);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\RateCode  $rateCode
     * @return \Illuminate\Http\Response
     */
    public function destroy(RateCode $rateCode)
    {
        if ($rateCode->business_id != $this->business()->id) {
            return new ErrorResponse(403, 'You do not have access to this rate code.');
        }

        try {
            if (!$rateCode->delete()) throw new \Exception();
            return new SuccessResponse('The rate code has been deleted');
        }
        catch (\Exception $e) {
            return new ErrorResponse(400, 'A rate code can only be deleted if it has not been assigned.');
        }
    }
}
