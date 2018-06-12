<?php

namespace App\Http\Controllers;

use App\EmergencyContact;
use App\Responses\ErrorResponse;
use App\User;
use Illuminate\Http\Request;

class EmergencyContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, User $user)
    {
        $contacts_count = EmergencyContact::where('user_id', $user->id)->count();

        if ($contacts_count >= 3) {
            return new ErrorResponse('403', 'Only 3 Emergency Contacts Allowed.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:80',
            'phone_number' => 'nullable|max:50',
            'relationship' => 'nullable|string|max:80'
        ]);

        $contact = $user->emergencyContacts()->create($data);

        return response()->json($contact);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\EmergencyContact  $contact
     * @return \Illuminate\Http\Response
     */
    public function show(EmergencyContact $contact)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\EmergencyContact  $contact
     * @return \Illuminate\Http\Response
     */
    public function edit(EmergencyContact $contact)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\EmergencyContact  $contact
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmergencyContact $contact)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\EmergencyContact  $contact
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmergencyContact $contact)
    {
        $this->authorize('delete', $contact);
        return response()->json($contact->delete());
    }
}
