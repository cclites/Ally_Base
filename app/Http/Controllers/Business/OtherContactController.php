<?php

namespace App\Http\Controllers\Business;

use App\OtherContact;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Http\Requests\UpdateOtherContactRequest;

class OtherContactController extends BaseController
{
    /**
     * Display a listing of the resource.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $query = OtherContact::forRequestedBusinesses()->ordered();
            return $query->get();
        }

        return view('business.contacts.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('business.contacts.show');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\UpdateOtherContactRequest  $request
     * @return \App\Responses\CreatedResponse
     * @throws \Exception
     */
    public function store(UpdateOtherContactRequest $request)
    {
        $data = $request->filtered();
        $this->authorize('create', [OtherContact::class, $data]);
        $contact = OtherContact::create($data);

        return new CreatedResponse('The contact has been created.', $contact, route('business.contacts.show', [$contact]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\OtherContact  $contact
     * @return \Illuminate\Http\Response
     */
    public function show(OtherContact $contact)
    { 
        $this->authorize('read', $contact);

        return view('business.contacts.show', compact('contact'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateOtherContactRequest  $request
     * @param \App\OtherContact $contact
     * @return \App\Responses\SuccessResponse
     * @throws \Exception
     */
    public function update(UpdateOtherContactRequest $request, OtherContact $contact)
    {
        $this->authorize('update', $contact);
        $data = $request->filtered();
        $contact->update($data);

        return new SuccessResponse('The contact has been updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\OtherContact  $contact
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(OtherContact $contact)
    {
        $this->authorize('delete', $contact);
        $contact->delete();

        return new SuccessResponse('The contact has been deleted.', [], route('business.contacts.index'));
    }
}
