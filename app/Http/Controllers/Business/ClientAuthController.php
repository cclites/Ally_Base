<?php
namespace App\Http\Controllers\Business;

use Auth;
use App\Billing\ClientAuthorization;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ClientAuthController extends BaseController
{
    /**
     * Display a list of services
     */
    // public function index()
    // {
    //     $query = Service::forAuthorizedChain()->ordered();
    //     $services = $query->get();
        
    //     return view('business.service', compact('services'));
    // }

    /**
     * Store a newly created service in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'service_id' => 'required|exists:services,id',
            'payer_id' => 'nullable|numeric',
            'effective_start' => 'required|date',
            'effective_end' => 'required|date',
            'units' => 'required|numeric',
            'unit_type' => 'required|string|max:10',
            'period' => 'required|string|max:10',
            'notes' => 'required|string',
        ]);
        
        $data['effective_start'] = Carbon::parse($data['effective_start']);
        $data['effective_end'] = Carbon::parse($data['effective_end']);

        $auth = ClientAuthorization::where('client_id', $request->client_id)->first();
        if ($auth != null) {
            $auth->update($data);
            return true;
        } else {
            $auth = ClientAuthorization::create($data);
            return true;
        }

        return false;
    }
}
