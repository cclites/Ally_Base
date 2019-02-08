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
        
        if($request->type != 'caregiver' && $request->type != 'client' && $request->type != 'all') {
            return new ErrorResponse(422, 'An error occured while trying to load custom fields, please try again.');
        }

        $fields = activeBusiness()->chain->fields;
        if($request->type != 'all') {
            $fields = $fields->where('user_type', $request->type)->values();
        }

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
        $data['key'] = preg_replace('/[^A-Za-z0-9]/', '', snake_case($request->label));
        $data['chain_id'] = activeBusiness()->chain_id;
        $this->authorize('update', $request->getBusiness());
        
        $alreadyExist = CustomField::where('chain_id', $data['chain_id'])
            ->where('label', $data['label'])
            ->where('user_type', $data['user_type'])
            ->first();

        if($alreadyExist) {
            return new ErrorResponse(500, 'A custom field with this label already exists. Please try again.');
        }

        if ($field = CustomField::create($data)) {
            return new SuccessResponse('Custom field has been created.', ['id' => $field->id]);
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
            $strippedString = preg_replace('/[^A-Za-z0-9]/', '', $option);
            CustomFieldOption::create([
                'field_id' => $field->id,
                'value' => snake_case($strippedString),
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
     * @return \App\Responses\ErrorResponse|\App\Responses\SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Packages\MetaData\Exceptions\ModelNotSavedException
     */
    public function storeValue(Request $request, string $account, string $id)
    {
        if($account != 'caregiver' && $account != 'client') {
            return new ErrorResponse(422, 'An error occured while trying to save your custom fields, please try again.');
        }

        if($account == 'caregiver') {
            $instance = Caregiver::findOrFail($id);
            $this->authorize('update', $instance);
        }else if($account == 'client') {
            $instance = Client::findOrFail($id);
            $this->authorize('update', $instance);
        }

        $customFields = activeBusiness()->chain->fields->where('user_type', $account);
        foreach ($customFields as $field) {
            $value = $request->input($field->key) ?: '';

            if($field->required && strlen($value) == 0) {
                return new ErrorResponse(422, 'The custom field '. $field->label . ' value is required.');
            }
            
            if($request->has($field->key) && $field->default_value !== $value) {
                $instance->setMeta($field->key, $value);
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
        $field = CustomField::findOrFail($id)->load('options');
        return view('business.custom_fields.show', compact('field'));
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
        $field = CustomField::findOrFail($id);
        $this->authorize('update', $field);

        if ($field->update($data)) {
            return new SuccessResponse('Custom field has been saved.', $field->fresh());
        }

        return new ErrorResponse(500, 'Could not save the custom field.  Please try again.');
    }

    /**
     * Update the options for the specified dropdown field
     *
     * @param \App\Http\Requests\UpdateCustomFieldOptionsRequest $request
     * @param \App\CustomField $field
     * @return \Illuminate\Http\Response
     */
    public function updateOptions(UpdateCustomFieldOptionsRequest $request, CustomField $field)
    {
        $this->authorize('update', $request->getBusiness());

        if($field->type != 'dropdown') {
            return new ErrorResponse(500, 'Could not create the custom field options for a field that is not a drop down.  Please try again.');
        }

        $data = $request->filtered();
        $options = array_unique(explode(',', $data['options']));
        $optionsKey = [];

        foreach ($options as $option) {
            $optionsKey[] = $key = preg_replace('/[^A-Za-z0-9]/', '', snake_case($option));
            CustomFieldOption::firstOrCreate([
                'field_id' => $field->id,
                'value' => $key,
                'label' => $option,
            ]);
        }

        # Create options (or select if already exist) then delete every option that wasnt submitted
        # since we're doing a replace-all type of updating
        CustomFieldOption::where('field_id', $field->id)
            ->whereNotIn('value', $optionsKey)
            ->delete();

        return $field->options;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $field = CustomField::findOrFail($id);
        $this->authorize('delete', $field);

        if ($field->delete()) {
            return new SuccessResponse('The field has been deleted.', null, route('business-settings').'#custom-fields');
        }

        return new ErrorResponse(500, 'Could not delete the field.  Please try again.');
    }
}
