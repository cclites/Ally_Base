<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Business\BaseController;
use App\Caregiver1099;
use App\Business;

class Admin1099Controller extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view_component('admin-1099-actions', '1099');
    }

    /**
     * Generates a list of emails for all registries and returns it in a view
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function RegistryEmailList()
    {
        $emails = Business::whereNotNull('contact_email')->pluck('contact_email')->toArray();
        $emailString = implode(",", $emails);

        return view_component('admin-registry-emails',
            'Registry Emails',
            ['emails' => $emailString],
            [
                'Home' => route('home'),
                '1099' => route('admin.admin-1099-actions')
            ]
        );
    }

    /**
     * @param Caregiver1099 $caregiver1099s
     * @param $year
     * @param $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function UserEmailsList(Caregiver1099 $caregiver1099s, $year, $role)
    {
        //Note: purposely not ignoring users who have 'no email' address.

        $data = $caregiver1099s->where('year', $year)->with(['client', 'caregiver'])
            ->get()
            ->map(function ($caregiver1099) use ($role) {
                if ($role = 'client') {
                    return $caregiver1099->client->email;
                }
                return $caregiver1099->caregiver->email;
            })
            ->implode(", ");

        return response()->json($data);
    }
}
