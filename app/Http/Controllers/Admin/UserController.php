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
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->expectsJson() && $request->input('json')) {
            $users = User::with(['paymentHold'])
                ->leftJoin($sql = \DB::raw("
                    (
                        SELECT 
                            u.id AS user_id,
                            b.id AS business_id,
                            b.name AS registry
                        FROM users AS u
                        LEFT JOIN clients AS c ON u.id = c.id
                        LEFT JOIN business_office_users AS bou ON u.id = bou.office_user_id
                        LEFT JOIN business_caregivers AS bcg ON u.id = bcg.caregiver_id
                        LEFT JOIN businesses AS b ON b.id = c.business_id 
                            OR b.id = bou.business_id 
                            OR b.id = bcg.business_id
                    ) AS user_business
                "), function($join) {
                    $join->on('user_business.user_id', '=', 'users.id');
                })
                ->orderBy('lastname')
                ->orderBy('firstname')
                ->get();
                            
            return $users;
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
    public function update(Request $request, User $user)
    {
        //
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
