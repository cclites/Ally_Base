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

        $data['priority'] = EmergencyContact::getNextPriorityForUser($user->id);

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
     * Change the priorty value for the given contact to the supplied value, and
     * re-order the rest of the contacts to fit the new value.
     *
     * @param Request $request
     * @param User $user
     * @param EmergencyContact $contact
     * @return \Illuminate\Http\Response
     */
    public function updatePriority(Request $request, User $user, EmergencyContact $contact)
    {
        $priority = $request->priority;
        if (empty($priority) || $priority < 1) {
            $priority = 1;
        }

        EmergencyContact::shiftPriorityDownAt($user->id, $priority, $contact->id);

        $contact->update(['priority' => $priority]);

        return response()->json($user->fresh()->emergencyContacts);
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

        EmergencyContact::shiftPriorityUpAt($contact->user_id, $contact->priority, $contact->id);

        $contact->delete();

        return response()->json($contact->user->fresh()->emergencyContacts);
    }
}
