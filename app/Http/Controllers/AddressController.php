<?php

namespace App\Http\Controllers;

use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Address;
use App\User;
use Illuminate\Http\Request;

/**
 * Class AddressController
 * Not meant to be called directly, used by other controllers.
 *
 * @package App\Http\Controllers
 */
class AddressController
{
    public function update(Request $request, User $user, $type, $reference = 'The address')
    {
        $data = $request->validate([
            'address1' => 'required',
            'address2' => 'nullable',
            'city'     => 'required',
            'state'    => 'required',
            'country'  => 'required|size:2',
            'county'   => 'nullable',
            'zip'      => 'required|min:5',
            'notes'    => 'nullable|max:255'
        ]);

        if( $data[ 'notes' ] ) $data[ 'notes' ] = filter_var( $data[ 'notes' ], FILTER_SANITIZE_STRING );

        \DB::beginTransaction();
        $address = $user->addresses->where('type', $type)->first();
        if ($address) {
            if ($address->update($data)) {
                if ($type != 'evv' || $geocode = $address->getGeocode(true)) {
                    \DB::commit();
                    return new SuccessResponse($reference . ' has been saved.');
                }
            }
        } else {
            $address = new Address($data);
            $address->type = $type;
            if ($user->addresses()->save($address)) {
                if ($type != 'evv' || $geocode = $address->getGeocode(true)) {
                    \DB::commit();
                    return new SuccessResponse($reference . ' has been saved.');
                }
            }
        }

        \DB::rollBack();
        if (isset($geocode) && $geocode === false) return new ErrorResponse(400, 'This address was found to be invalid.  Please verify and try again.');
        return new ErrorResponse(500, $reference . ' could not be saved.');
    }
}
