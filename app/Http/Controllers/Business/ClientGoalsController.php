<?php

namespace App\Http\Controllers\Business;

use App\CarePlan;
use App\Responses\ConfirmationResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;
use App\Client;
use App\ClientGoal;
use Illuminate\Http\Response;
use Barryvdh\Snappy\PdfWrapper;

class ClientGoalsController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Client $client
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Client $client)
    {
        $this->authorize('read', $client);

        return response()->json($client->goals()->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param \App\Client $client
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $data = $request->validate(
            [
                'question' => 'required|max:255',
                'track_goal_progress' => 'required|boolean',
            ],
            [
                'question.required' => 'A Goal must have a question.',
                'question.max' => 'A question must be less than 255 characters.',
            ]
        );

        if ($goal = $client->goals()->create($data)) {
            return new SuccessResponse('The goal has been created.', $goal);
        }

        return new ErrorResponse(500, 'Unable to create goal.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ClientGoal  $goal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Client $client, ClientGoal $goal)
    {
        $this->authorize('update', $client);

        $data = $request->validate(
            [
                'question' => 'required|max:255',
                'track_goal_progress' => 'required|boolean',
            ],
            [
                'question.required' => 'A Goal must have a question.',
                'question.max' => 'A question must be less than 255 characters.',
            ]
        );

        if ($goal->update($data)) {
            return new SuccessResponse('The goal has been updated.', $goal->fresh());
        }

        return new ErrorResponse(500, 'The goal could not be saved.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ClientGoal  $goal
     * @return \Illuminate\Http\Response
     */
    public function destroy(Client $client, ClientGoal $goal)
    {
        $this->authorize('update', $client);

        if ($goal->delete()) {
            return new SuccessResponse('The goal has been deleted.', []);
        }

        return new ErrorResponse(500, 'The goal could not be deleted.');
    }

    public function show($client){
        return Client::find($client)->with(['goals']);
    }

    public function generatePdf(Request $request, Client $client)
    {
        $this->authorize('read', $client);

        $image = asset('/images/background1.jpg');

        $html = response(view('business.client.client_goals', ['client'=>$client, 'image'=>$image]))->getContent();

        $snappy = \App::make('snappy.pdf');
        return new Response(
            $snappy->getOutputFromHtml($html),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $client->nameLastFirst() . '_client_goals.pdf"'
            )
        );
    }
}
