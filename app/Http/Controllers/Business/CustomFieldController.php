<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\CustomField;
use App\Business;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCustomFieldRequest;
use App\Responses\SuccessResponse;
use App\Responses\ErrorResponse;

class CustomFieldController extends Controller
{
    /**
     * Display a listing of the resource. 
     *
     * @param App\Business $business
     * @return \Illuminate\Http\Response
     */
    public function index(Business $business)
    {
        $this->authorize('update', $business);
        return response()->json(activeBusiness()->custom_fields);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('business.custom_fields.show');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCustomFieldRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UpdateCustomFieldRequest $request)
    {
        $data = $request->filtered();
        $data['key'] = snake_case($request->label);
        $data['chain_id'] = activeBusiness()->chain_id;
        $this->authorize('update', $request->getBusiness());

        if ($field = CustomField::create($data)) {
            return new SuccessResponse('Custom field has been created.', $field);
        }

        return new ErrorResponse(500, 'Could not create the custom field.  Please try again.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCustomFieldRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCustomFieldRequest $request, $id)
    {
        $data = $request->filtered();
        $this->authorize('update', $request->getBusiness());

        if ($field->update($data)) {
            return new SuccessResponse('Custom field has been saved.', $field->fresh());
        }

        return new ErrorResponse(500, 'Could not save the custom field.  Please try again.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('delete', $field->business);

        if ($field->delete()) {
            return new SuccessResponse('The field has been deleted.');
        }

        return new ErrorResponse(500, 'Could not delete the field.  Please try again.');
    }
}
