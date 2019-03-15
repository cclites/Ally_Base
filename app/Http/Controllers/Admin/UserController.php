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
            $perPage = $request->input('perpage', 20);
            $page = $request->input('page', 1);
            $sortBy = $request->input('sort', 'lastname');
            $sortOrder = $request->input('desc', false) == 'true' ? 'desc' : 'asc';
            $offset = ($page - 1) * $perPage;
            $chainFilter = $request->input('chain', null);
            $search = $request->input('search', null);

            switch ($sortBy) {
                case 'id':
                case 'email':
                case 'firstname':
                case 'username':
                case 'role_type':
                case 'created_at':
                    $order = "users.$sortBy $sortOrder";
                    break;
                case 'lastname':
                default:
                    $order = "users.lastname $sortOrder, users.firstname $sortOrder";
                    break;
            }

            $query = User::with('paymentHold', 'caregiver', 'caregiver.businessChains', 'client', 'client.business.businessChain', 'officeUser', 'officeUser.businessChain')
                ->whereIn('role_type', ['client', 'caregiver', 'office_user']);

            if (! empty($chainFilter)) {
                $query->where(function ($query) use ($chainFilter) {
                    $query->whereHas('client', function ($q) use ($chainFilter) {
                        $q->whereHas('business', function ($q2) use ($chainFilter) {
                            $q2->where('businesses.chain_id', $chainFilter);
                        });
                    })->orWhereHas('caregiver', function ($q) use ($chainFilter) {
                        $q->whereHas('businessChains', function ($q2) use ($chainFilter) {
                            $q2->where('business_chains.id', $chainFilter);
                        });
                    })->orWhereHas('officeUser', function ($q) use ($chainFilter) {
                        $q->whereHas('businessChain', function ($q2) use ($chainFilter) {
                            $q2->where('business_chains.id', $chainFilter);
                        });
                    });
                });
            }

            if (! empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('users.username', 'LIKE', "%$search%")
                        ->orWhere('users.email', 'LIKE', "%$search%")
                        ->orWhere('users.id', 'LIKE', "%$search%")
                        ->orWhere('users.firstname', 'LIKE', "%$search%")
                        ->orWhere('users.lastname', 'LIKE', "%$search%")
                        ->orWhere('users.role_type', 'LIKE', "%$search%");
                });
            }

            $total = $query->count();
            $users = $query->orderByRaw($order)
                ->offset($offset)
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
