<?php

namespace App\Http\Controllers\Business;

use App\Caregiver;
use App\CaregiverLicense;
use App\ExpirationType;
use App\Http\Requests\SaveCaregiverExpirationsRequest;
use App\Notifications\LicenseExpirationReminder;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CaregiverLicenseController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @param Caregiver $caregiver
     * @return CaregiverLicense[]|\Illuminate\Database\Eloquent\Collection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Caregiver $caregiver)
    {
        $this->authorize('read', $caregiver);

        return $caregiver->licenses;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Caregiver $caregiver
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request, Caregiver $caregiver)
    {
        $this->authorize( 'update', $caregiver );

        $data = $request->validate([

            'name'                     => 'required|max:200',
            'description'              => 'nullable',
            'expires_at'               => 'required|date',
            'chain_expiration_type_id' => 'nullable',
        ]);

        $data[ 'expires_at' ] = filter_date( $data[ 'expires_at' ] );

        $license = new CaregiverLicense( $data );
        if ( $caregiver->licenses()->save( $license ) ) {

            return new SuccessResponse( 'Action Successful.', $license->toArray() );
        }
        return new ErrorResponse( 500, 'Action Unsuccessful.' );
    }

    public function saveMany( SaveCaregiverExpirationsRequest $request, Caregiver $caregiver )
    {
        $this->authorize( 'update', $caregiver );

        $expirations = $request->validated(); //[ '*' ];

        \DB::beginTransaction();
        try {

            $results = $caregiver->licenses()->saveMany( array_map( function( $exp ) use ( $caregiver ){

                $element = CaregiverLicense::findOrNew( $exp[ 'id' ] ?? null );
                $element[ 'name'         ] = $exp[ 'name' ];
                $element[ 'description'  ] = $exp[ 'description' ] ?? null;
                $element[ 'caregiver_id' ] = $caregiver->id;
                $element[ 'expires_at'   ] = Carbon::parse( $exp[ 'expires_at' ] )->format( 'Y-m-d' );
                $element[ 'updated_at'   ] = now()->format( 'Y-m-d H:i:s' );

                $element[ 'chain_expiration_type_id' ] = $exp[ 'chain_expiration_type_id' ] ?? null;

                return $element;
            }, $expirations ));

            \DB::commit();
            return new SuccessResponse( 'Caregiver expirations saved successfully.', $results );
        } catch ( \Exception $ex ) {
            \Log::error($ex->getMessage());
            \DB::rollBack();
            return new ErrorResponse( 500, $ex->getMessage() );
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Caregiver $caregiver
     * @param \App\CaregiverLicense $license
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update( Request $request, Caregiver $caregiver, CaregiverLicense $license )
    {
        $this->authorize( 'update', $caregiver );

        $data = $request->validate([

            'name'        => 'required|max:200',
            'description' => 'nullable',
            'expires_at'  => 'required|date',
        ]);

        $data[ 'expires_at' ] = filter_date( $data[ 'expires_at' ] );

        if ( $license->update( $data ) ) {

            return new SuccessResponse( 'The license has been updated.', $license->toArray() );
        }
        return new ErrorResponse( 500, 'The license could not be updated.' );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Caregiver $caregiver
     * @param \App\CaregiverLicense $license
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Caregiver $caregiver, CaregiverLicense $license)
    {
        $this->authorize('update', $caregiver);

        if ($license->delete()) {
            return new SuccessResponse('The license has been deleted.');
        }
        return new ErrorResponse(500, 'The license could not be deleted.');
    }

    /**
     * Send notification about expiring license to the Caregiver.
     *
     * @param CaregiverLicense $license
     * @return SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function expirationReminder(CaregiverLicense $license)
    {
        $this->authorize('read', $license->caregiver);

        // TODO: figure out which business is asking
        $business = $license->caregiver->businesses->first();

        $license->caregiver->user->notify(new LicenseExpirationReminder($business, $license));

        return new SuccessResponse('A reminder email has been sent.');
    }
}
