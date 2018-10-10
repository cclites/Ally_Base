<?php

namespace App\Http\Controllers\Business;

use App\Http\Requests\UpdateProspectRequest;
use App\Prospect;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;

class ProspectController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $query = $this->business()->prospects()->orderBy('lastname')->orderBy('firstname');
            return $query->get();
        }

        return view('business.prospects.index');
    }

    /**
     * Display the form to create specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('business.prospects.show');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\UpdateProspectRequest $request
     * @return \App\Responses\CreatedResponse
     * @throws \Exception
     */
    public function store(UpdateProspectRequest $request)
    {
        $data = $request->filtered();
        $prospect = $this->business()->prospects()->create($data);

        return new CreatedResponse('The prospect has been created.', $prospect, route('business.prospects.show', [$prospect]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Prospect  $prospect
     * @return \Illuminate\Http\Response
     */
    public function show(Prospect $prospect)
    {
        if (!$this->businessHasProspect($prospect)) {
            abort(403);
        }

        return view('business.prospects.show', compact('prospect'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateProspectRequest $request
     * @param  \App\Prospect $prospect
     * @return \App\Responses\ErrorResponse|\App\Responses\SuccessResponse
     */
    public function update(UpdateProspectRequest $request, Prospect $prospect)
    {
        if (!$this->businessHasProspect($prospect)) {
            return new ErrorResponse(403, 'You do not have access to this prospect.');
        }

        $data = $request->filtered();
        $prospect->update($data);

        return new SuccessResponse('The prospect has been updated.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Prospect  $prospect
     * @return \Illuminate\Http\Response
     */
    public function convert(Prospect $prospect)
    {
        if (!$this->businessHasProspect($prospect)) {
            return new ErrorResponse(403, 'You do not have access to this prospect.');
        }

        $username = $prospect->email ?? str_slug($prospect->name) . mt_rand(10,999);
        if (!$client = $prospect->convert($username)) {
            return new ErrorResponse(400, 'The client record could not be created.');
        }

        return new CreatedResponse('The client record has been created.', $client, route('business.clients.show', [$client]));
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Prospect $prospect
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Prospect $prospect)
    {
        if (!$this->businessHasProspect($prospect)) {
            return new ErrorResponse(403, 'You do not have access to this prospect.');
        }

        $prospect->delete();
        return new SuccessResponse('The prospect has been deleted.', [], route('business.prospects.index'));
    }
}
