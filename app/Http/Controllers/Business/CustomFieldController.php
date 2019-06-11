<?php

namespace App\Http\Controllers\Business;

use App\Http\Requests\StoreCustomFieldRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use App\CustomField;
use App\CustomFieldOption;
use App\Caregiver;
use App\Client;
use App\Http\Requests\UpdateCustomFieldRequest;
use App\Http\Requests\UpdateCustomFieldOptionsRequest;
use App\Responses\SuccessResponse;
use App\Responses\ErrorResponse;

class CustomFieldController extends BaseController
{
    /**
     * Display a listing of the resource. 
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('read', $this->businessChain());

        $request->validate([
            'type' => 'required|in:all,caregiver,client',
        ]);

        $query = $this->businessChain()->fields()->with('options');

        if ($request->type != 'all') {
            $query->where('user_type', $request->type);
        }

        return response()->json($query->get()->values());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view_component('custom-field-edit', 'Create a Custom Field', [],
            [
                'Home' => route('home'),
                'Settings' => route('business-settings'),
                'Custom Fields' => route('business-settings').'#custom-fields',
            ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCustomFieldRequest  $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function store(StoreCustomFieldRequest $request)
    {
        $this->authorize('update', $this->businessChain());

        $data = $request->filtered();

        if (CustomField::findDuplicate($this->businessChain(), $data['user_type'], $data['label'], $data['key'])) {
            return new ErrorResponse(500, 'A custom field with this label already exists. Please try again.');
        }

        \DB::beginTransaction();

        if ($field = $this->businessChain()->fields()->create($data)) {
            if ($field->isDropdown()) {
                foreach ($data['options'] as $option) {
                    $field->options()->create([
                        'label' => $option,
                        'value' => CustomFieldOption::getValueFromLabel($option),
                    ]);
                }
            }

            \DB::commit();
            return new SuccessResponse('Custom field has been created.', ['id' => $field->id]);
        }

        \DB::rollBack();
        return new ErrorResponse(500, 'Could not create the custom field.  Please try again.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CustomField $customField
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(CustomField $customField)
    {
        $this->authorize('read', $customField);

        $customField->load('options');

        return view_component('custom-field-edit', 'Edit Custom Field', ['field' => $customField],
            [
                'Home' => route('home'),
                'Settings' => route('business-settings'),
                'Custom Fields' => route('business-settings').'#custom-fields',
            ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCustomFieldRequest  $request
     * @param  \App\CustomField  $customField
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function update(UpdateCustomFieldRequest $request, CustomField $customField)
    {
        $this->authorize('update', $customField);

        $data = $request->filtered();

        if (CustomField::findDuplicate($this->businessChain(), $data['user_type'], $data['label'], $customField->key, $customField->id)) {
            return new ErrorResponse(500, 'A custom field with this label already exists. Please try again.');
        }

        \DB::beginTransaction();

        if ($customField->update($data)) {
            $optionsToRemove = CustomFieldOption::findMissingIds($customField, $data['options']);

            if ($customField->isDropdown()) {
                foreach ($data['options'] as $label) {
                    $value = CustomFieldOption::getValueFromLabel($label);
                    if ($existingOption = $customField->options->where('value', $value)->first()) {
                        // Update label only (would really only occur if user changes case)
                        $existingOption->update([
                            'label' => $label,
                        ]);
                    } else {
                        $customField->options()->create([
                            'label' => $label,
                            'value' => $value,
                        ]);
                    }
                }
            }

            $customField->options()->whereIn('id', $optionsToRemove)->delete();

            \DB::commit();
            return new SuccessResponse('Custom field has been saved.', $customField->fresh()->load('options'));
        }

        \DB::rollBack();
        return new ErrorResponse(500, 'Could not save the custom field.  Please try again.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CustomField  $customField
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(CustomField $customField)
    {
        $this->authorize('delete', $customField);

        if ($customField->delete()) {
            return new SuccessResponse('The field has been deleted.', null, route('business-settings').'#custom-fields');
        }

        return new ErrorResponse(500, 'Could not delete the field.  Please try again.');
    }
}
