<?php

namespace App\Http\Controllers\Admin;

use App\Business;
use App\OfficeUser;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Rules\ValidTimezoneOrOffset;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $businesses = Business::orderBy('name')->get();

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
        return view('admin.businesses.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $businessData = $request->validate([
            'name' => 'required',
            'address1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'phone1' => 'required',
            'timezone' => ['required', new ValidTimezoneOrOffset()],
        ]);
        $businessData['country'] = 'US';

        $userData = $request->validate([
            'email' => 'required|email',
            'username' => 'nullable|unique:users',
            'firstname' => 'required',
            'lastname' => 'required',
            'password' => 'required|confirmed',
        ]);
        $userData['username'] = $userData['username'] ?? $userData['email'];
        $userData['password'] = bcrypt($userData['password']);

        $business = Business::create($businessData);
        if (!$business) return new ErrorResponse(500, 'Unable to create business');

        $user = OfficeUser::create($userData);
        if (!$user) return new ErrorResponse(500, 'Unable to create office user');

        $business->users()->attach($user);
        return new CreatedResponse('The business and office user have been created.', [], route('admin.businesses.show', [$business->id]));
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
            'name' => 'required',
            'address1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'phone1' => 'required',
            'timezone' => ['required', new ValidTimezoneOrOffset()],
        ]);

        if ($business->update($businessData)) {
            return new SuccessResponse('The business has been saved.');
        }
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
}
