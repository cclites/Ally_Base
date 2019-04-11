<?php

namespace App\Http\Controllers\Business;

use App\Caregiver;
use App\CaregiverRestriction;
use Illuminate\Http\Request;

class BusinessCaregiverRestrictionController extends BaseController
{
    /**
     * Get a listing of the Caregiver's restrictions.
     *
     * @param Caregiver $caregiver
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Caregiver $caregiver)
    {
        $this->authorize('read', $caregiver);

        return response()->json(
            $caregiver->restrictions
        );
    }

    /**
     * Store a new CaregiverRestriction.
     *
     * @param Request $request
     * @param Caregiver $caregiver
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request, Caregiver $caregiver)
    {
        $this->authorize('update', $caregiver);

        $data = $request->validate(['description' => 'required|string|max:255']);

        $caregiver->restrictions()->create($data);

        return response()->json(
            $caregiver->fresh()->restrictions
        );
    }

    /**
     * Update an existing CaregiverRestriction.
     *
     * @param Request $request
     * @param Caregiver $caregiver
     * @param CaregiverRestriction $restriction
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, Caregiver $caregiver, CaregiverRestriction $restriction)
    {
        $this->authorize('update', $caregiver);

        $data = $request->validate(['description' => 'required|string|max:255']);

        $restriction->update($data);

        return response()->json(
            $caregiver->fresh()->restrictions
        );
    }

    /**
     * Delete an existing CaregiverRestriction.
     *
     * @param Caregiver $caregiver
     * @param CaregiverRestriction $restriction
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Caregiver $caregiver, CaregiverRestriction $restriction)
    {
        $this->authorize('update', $caregiver);

        $restriction->delete();

        return response()->json(
            $caregiver->fresh()->restrictions
        );
    }
}
