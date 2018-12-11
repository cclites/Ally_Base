<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\CustomField;
use App\CustomFieldOption;
use App\Business;
use App\Caregiver;
use App\Client;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCustomFieldRequest;
use App\Http\Requests\UpdateCustomFieldOptionsRequest;
use App\Responses\SuccessResponse;
use App\Responses\ErrorResponse;

class CustomFieldController extends Controller
{
    /**
     * Display a listing of the resource. 
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('update', activeBusiness());
        
        if($request->type != 'caregiver' && $request->type != 'client') {
            return new ErrorResponse(422, 'An error occured while trying to load custom fields, please try again.');
        }

        $fields = activeBusiness()->chain->fields;
        $fields = $fields->where('user_type', $request->type)->values();
        $fields = $fields->map(function(CustomField $field) {
            $field->options = $field->options;
            return $field;
        });

        return response()->json($fields);
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
        
        $alreadyExist = CustomField::where('chain_id', $data['chain_id'])
            ->where('label', $data['label'])
            ->where('user_type', $data['user_type'])
            ->first();

        if($alreadyExist) {
            return new ErrorResponse(500, 'This custom field already exists. Please try again.');
        }

        if ($field = CustomField::create($data)) {
            if($field->required) {
                // Set the given default value on all users if the field is required
                $entities= $request->user_type == 'client'
                    ? Client::forRequestedBusinesses()->get()
                    : Caregiver::forRequestedBusinesses()->get();

                $entities->each(function($entry) use($field) {
                    $entry->setMeta($field->key, $field->default_value);
                });
            }

            return new SuccessResponse('Custom field has been created.', $field);
        }

        return new ErrorResponse(500, 'Could not create the custom field.  Please try again.');
    }

    /**
     * Store the newly created options for a custom dropdown field
     *
     * @param \App\Http\Requests\UpdateCustomFieldOptionsRequest $request
     * @param \App\CustomField $field
     * @return \Illuminate\Http\Response
     */
    public function storeOptions(UpdateCustomFieldOptionsRequest $request, CustomField $field)
    {
        $this->authorize('update', $request->getBusiness());

        if($field->type != 'dropdown') {
            return new ErrorResponse(500, 'Could not create the custom field options for a field that is not a drop down.  Please try again.');
        }

        $data = $request->filtered();
        $options = array_unique(explode(',', $data['options']));
        foreach ($options as $option) {
            CustomFieldOption::create([
                'field_id' => $field->id,
                'value' => snake_case($option),
                'label' => $option,
            ]);
        }

        return $field->options;
    }

    /**
     * Store/Update the value for a custom field on a caregiver or client
     *
     * @param \Illuminate\Http\Request $request
     * @param string $account The type of account to service
     * @param string $id The ID of the account
     * @return void
     */
    public function storeValue(Request $request, string $account, string $id)
    {
        if($account != 'caregiver' && $account != 'client') {
            return new ErrorResponse(422, 'An error occured while trying to save your custom fields, please try again.');
        }

        $instance;
        if($account == 'caregiver') {
            $instance = Caregiver::findOrFail($id);
            $this->authorize('update', $instance);
        }else if($account == 'client') {
            $instance = Client::findOrFail($id);
            $this->authorize('update', $instance);
        }
            
        $customFields = activeBusiness()->chain->fields->pluck('key');

        foreach ($customFields as $key) {
            if($request->has($key) && $request->input($key)) {
                $instance->setMeta($key, $request->input($key));
            }
        }

        return new SuccessResponse('Your custom field values were successfully saved.');
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
