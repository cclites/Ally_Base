<?php

namespace App\Http\Controllers\Business;

use App\Business;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\SalesPerson;
use Illuminate\Http\Request;
use App\Client;

use Log;

class SalesPersonController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function index(Business $business)
    {
        return response()->json($business->salesPeople);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'business_id' => 'required|int',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|email',
            'active' => 'required|bool',
        ]);

        $this->authorize('create', [SalesPerson::class, $data]);
        
        $salesPerson = SalesPerson::create($data);
        return new SuccessResponse('Sales person created', $salesPerson);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SalesPerson  $salesPerson
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SalesPerson $salesPerson)
    {
        $this->authorize('update', $salesPerson);

        $data = $request->validate([
            'business_id' => 'required|int',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|email',
            'active' => 'required|bool',
        ]);

        $salesPerson->update($data);
        return new SuccessResponse('Sales person updated', $salesPerson);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SalesPerson  $salesPerson
     * @return \Illuminate\Http\Response
     */
    public function destroy(SalesPerson $salesPerson)
    {
        $this->authorize('delete', $salesPerson);

        if (Client::where('sales_person_id', $salesPerson->id)->exists()) {
            return new ErrorResponse(400, 'Unable to delete sales person because they are assigned to a client.');
        }

        $salesPerson->delete();
        return new SuccessResponse('Salesperson deleted.');
    }
}
