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
            return EmailTemplate::where('business_id', $request->business_id)->get()->toArray();
        }

        $types = [];
        foreach(EmailTemplate::TEMPLATE as $type=>$value){
            $types[] = ['id'=>$value, 'name'=> ucwords(str_replace("_", " ", $value))];
        }

        return view_component('email-templates', 'Custom Email Templates', compact('types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ErrorResponse
     * @return ErrorResponse
     */
    public function store(Request $request)
    {
        $template = new EmailTemplate($request->all());

        if($template->save()){
            return new SuccessResponse( 'Template has been saved.', $template );
        }

        return new ErrorResponse(500, 'Unable to save template.');
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
    public function update(UpdateEmailTemplateRequest $request)
    {
        $business = $request->getBusiness();
        $request->authorize('update', $business);
        $template = EmailTemplate::find($request->id);

        if( $template->update( $request->filtered() ) ){
            return new SuccessResponse( 'Template has been updated.', $template );
        }

        return new ErrorResponse(500, 'Unable to update template.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        EmailTemplate::destroy($id);
        return new SuccessResponse('Template has been deleted.');
    }
}
