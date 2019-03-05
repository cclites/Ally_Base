<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Business;

class OfficeUserController extends BaseController
{
    /**
     * Get a listing of the office users belonging to the active business chain.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $officeUsers = $this->businessChain()
            ->users()
            ->ordered()
            ->with('businesses')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'business_ids' => $item->businesses->pluck('id'),
                    'name' => $item->name,
                    'nameLastFirst' => $item->nameLastFirst,
                ];
        });

        return response()->json($officeUsers);
    }

    /**
     * Get a listing of office users belonging to the provided business.
     *
     * @param Business $business
     * @return void
     */
    public function listForBusiness(Business $business)
    {
        $this->authorize('read', $business);

        $officeUsers = $business->users()
            ->get()
            ->sortBy('name')
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'nameLastFirst' => $item->nameLastFirst,
                ];
            });

        return response()->json($officeUsers);
    }
}
