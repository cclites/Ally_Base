<?php
namespace App\Http\Controllers\Caregivers;

use App\Client;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;
use App\ClientNarrative;

class ClientNarrativeController extends BaseController
{
    /**
     * List the entire client narrative.
     *
     * @param \Illuminate\Http\Request
     * @param \App\Client $client
     * @return \App\ClientNarrative[]|\Illuminate\Database\Eloquent\Collection
     */
    public function index(Request $request, Client $client)
    {
        if (! $this->caregiver()->clients()->where('client_id', $client->id)->exists()) {
            abort(403);
        }

        if ($request->expectsJson() && $request->has('json')) {
            $narrative = $client->narrative()->paginate($request->per_page);
            return response()->json($narrative);
        }

        return view('caregivers.client_narrative', compact(['client']));
    }

    /**
     * Store new narrative notes.
     *
     * @param Request $request
     * @param Client $client
     * @return ErrorResponse|SuccessResponse
     */
    public function store(Request $request, Client $client)
    {
        if (! $this->caregiver()->clients()->where('client_id', $client->id)->exists()) {
            abort(403);
        }

        $request->validate([
            'notes' => 'required|min:1|max:63000',
        ]);

        $narrative = $client->narrative()->create([
            'notes' => $request->notes,
            'creator_id' => auth()->id(),
        ]);

        if (! $narrative) {
            return new ErrorResponse(500, 'An unexpected error occurred, please try again.');
        }

        return new SuccessResponse('Your notes have been saved to the Client Narrative.', $narrative);
    }

    /**
     * Delete narrative notes.
     *
     * @param Request $request
     * @param Client $client
     * @return ErrorResponse|SuccessResponse
     */
    public function destroy(Request $request, Client $client, ClientNarrative $narrative)
    {
        if (! $this->caregiver()->clients()->where('client_id', $client->id)->exists()) {
            abort(403);
        }

        if ($narrative->delete()) {
            return new SuccessResponse('Narrative notes were successfully deleted.');
        }   
        
        return new ErrorResponse(500, 'An unexpected error occurred, please try again.');
    }
}