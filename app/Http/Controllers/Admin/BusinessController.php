<?php

namespace App\Http\Controllers\Admin;

use App\Actions\CreateBusiness;
use App\Actions\CreateBusinessChain;
use App\Business;
use App\BusinessChain;
use App\OfficeUser;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Rules\ValidTimezoneOrOffset;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\PhoneNumber;

class BusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $businesses = Business::with(['paymentHold', 'chain'])->orderBy('name')->get();

        if ($request->expectsJson()) {
            return $businesses;
        }

        return view('admin.businesses.index', compact('businesses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $chains = BusinessChain::ordered()->get();

        return view('admin.businesses.create', compact('chains'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param \App\Actions\CreateBusiness $action
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function store(Request $request, CreateBusiness $action, CreateBusinessChain $chainAction)
    {
        \DB::beginTransaction();

        $businessData = $request->validate([
            'name' => 'required|string|max:64',
            'short_name' => 'required|string|max:45',
            'address1' => 'string|nullable',
            'city' => 'string|nullable',
            'state' => 'string|nullable',
            'zip' => 'string|nullable',
            'phone1' => 'string|nullable',
            'type' => 'string',
            'timezone' => ['required', new ValidTimezoneOrOffset()],
            'chain_id' => 'nullable|exists:business_chains,id',
        ]);
        $businessData['country'] = 'US';
        $businessData['sce_shifts_in_progress'] = false;  // OVERRIDE TO ALWAYS DISABLE THIS OPTION

        $request->validate([
            'chain_id' => 'nullable|exists:business_chains,id',
            'new_chain_name' => 'required_without:chain_id|nullable|string|max:70'

        ], ['*' => 'You must select, or create, a business chain.']);

        if (!$request->input('chain_id')) {
            $chain = $chainAction->create([
                'name' => $request->new_chain_name,
                'slug' => BusinessChain::generateSlug($request->new_chain_name),
                'address1' => $request->address1,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
                'phone1' => $request->phone1,
            ]);
            $businessData['chain_id'] = $chain->id;
        }

        $business = $action->create($businessData);
        if (!$business) return new ErrorResponse(500, 'Unable to create business');

        \DB::commit();
        return new CreatedResponse('The business has been created.', [], route('admin.businesses.show', [$business->id]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Business  $business
     * @return \Illuminate\Http\Response
     */
    public function show(Business $business)
    {
        return view('admin.businesses.show', compact('business'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Business  $business
     * @return \Illuminate\Http\Response
     */
    public function edit(Business $business)
    {
        return $this->show($business);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Business  $business
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Business $business)
    {
        $businessData = $request->validate([
            'name' => 'required|string|max:64',
            'short_name' => 'required|string|max:45',
            'address1' => 'string|nullable',
            'city' => 'string|nullable',
            'state' => 'string|nullable',
            'zip' => 'string|nullable',
            'phone1' => 'string|nullable',
            'type' => 'string',
            'timezone' => ['required', new ValidTimezoneOrOffset()],
        ]);

        if ($business->update($businessData)) {
            return new SuccessResponse('The business has been saved.');
        }
    }

    public function updateContactInfo(Request $request, Business $business)
    {
        $data = $request->validate([
            'contact_name' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string'
        ]);

        $business->update($data);

        return new SuccessResponse('Business contact info updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Business  $business
     * @return \Illuminate\Http\Response
     */
    public function destroy(Business $business)
    {
        //
    }

    public function addHold(Business $business)
    {
        $business->addHold();
        return new SuccessResponse('A payment hold has been placed on ' . $business->name);
    }

    public function removeHold(Business $business)
    {
        $business->removeHold();
        return new SuccessResponse('The payment hold has been removed from ' . $business->name);
    }

    /**
     * Allow an administrator to manually switch the active business they are manipulating
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Responses\SuccessResponse
     */
    public function setActiveBusiness(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
        ]);

        $activeBusiness = app()->make(\App\ActiveBusiness::class);
        $activeBusiness->set(Business::find($request->business_id));
        return new SuccessResponse('The active business has been switched.');
    }

    /**
     * Update the providers SMS settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Business  $business
     * @return \Illuminate\Http\Response
     */
    public function updateSmsSettings(Request $request, Business $business, PhoneNumber $phone)
    {
        $data = $request->validate([
            'outgoing_sms_number' => 'string|nullable',
        ]);

        // always formats phone to national number
        $phone->input($data['outgoing_sms_number']);

        if ($business->update(['outgoing_sms_number' => $phone->national_number])) {
            return new SuccessResponse('The business has been saved.');
        }
    }
}
