<?php

namespace App\Http\Controllers\Business;

use App\Business;
use Illuminate\Http\Request;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Http\Controllers\Controller;
use App\EmailTemplate;
use App\Http\Requests\UpdateEmailTemplateRequest;

class EmailTemplateController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->json){
            $business = Business::find($request->business_id);
            $this->authorize('read', $business);

            return EmailTemplate::where('business_id', $request->business_id)->get()->toArray();
        }

        $types = [];

        foreach(EmailTemplate::AVAILABLE_CUSTOM_TEMPLATES as $type=>$value){
            $types[] = ['id'=>$value, 'name'=> ucwords(str_replace("_", " ", $value))];
        }

        return view_component('email-templates', 'Custom Email Templates', compact('types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function store(UpdateEmailTemplateRequest $request)
    {
        if ($template = EmailTemplate::create($request->filtered())) {
            return new SuccessResponse('Template has been created.', $template);
        }

        return new ErrorResponse(500, 'An unexpected error occurred while trying to create a Template.  Please try again.');

    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($type, $id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateEmailTemplateRequest $request, EmailTemplate $template)
    {
        $request->authorize('update', $template);

        if( $template->update($request->filtered() ) ){
            return new SuccessResponse('Templates has been updated.', $template->fresh() );
        }

        return new ErrorResponse(500, 'An unexpected error occurred while trying to update a Template.  Please try again.');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmailTemplate $template)
    {
        $this->authorize('delete', $template);

        if($template->delete()){
            return new SuccessResponse('Template has been deleted.');
        }

        return new ErrorResponse(500, 'Unable to update template.');
    }
}
