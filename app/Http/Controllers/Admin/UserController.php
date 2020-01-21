<?php

namespace App\Http\Controllers\Admin;

use App\Responses\SuccessResponse;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->expectsJson() && $request->has('json')) {
            $perPage = $request->input('perPage', 20);
            $page = $request->input('page', 1);
            $sortBy = $request->input('sort', 'lastname');
            $sortOrder = $request->input('desc', false) == 'true' ? 'desc' : 'asc';
            $offset = ($page - 1) * $perPage;
            $search = $request->input('search', null);

            $query = User::with('paymentHold',
                    'caregiver',
                    'caregiver.businessChains',
                    'client',
                    'client.business.businessChain',
                    'officeUser',
                    'officeUser.businessChain',
                    'userAdminNotesAsSubject'
                )
                ->whereIn('role_type', ['client', 'caregiver', 'office_user'])
                ->search($search);

            if ($chainFilter = $request->input('chain', null)) {
                $query->forChain($chainFilter);
            }

            $total = $query->count();

            if ($sortBy == 'lastname' || $sortBy == 'null') {
                $query->orderByRaw("users.lastname $sortOrder, users.firstname $sortOrder");
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }

            $users = $query->offset($offset)
                ->limit($perPage)
                ->get()
                ->map(function ($user) {
                    $data = [
                        'id' => $user->id,
                        'firstname' => $user->firstname,
                        'lastname' => $user->lastname,
                        'username' => $user->username,
                        'email' => $user->email,
                        'role_type' => $user->role_type,
                        'created_at' => $user->created_at->toDateTimeString(),
                        'chain_id' => optional($user->getChain())->id,
                        'chain_name' => optional($user->getChain())->name,
                        'payment_hold' => $user->payment_hold,
                        'admin_notes' => $user->userAdminNotesAsSubject,
                    ];

                    return $data;
                });

            return response()->json([
                'total' => $total,
                'results' => $users,
            ]);
        }

        return view('admin.users.index');
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update( Request $request, User $user )
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }

    public function addHold(User $user)
    {
        $user->addHold();
        return new SuccessResponse('A payment hold has been placed on ' . $user->name());
    }

    public function removeHold(User $user)
    {
        $user->removeHold();
        return new SuccessResponse('The payment hold has been removed from ' . $user->name());
    }
}
