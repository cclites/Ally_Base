<?php
namespace App\Http\Controllers\Business;

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
        $this->authorize('read', $client);

        $narrative = $client->narrative()->paginate($request->per_page);
        return response()->json($narrative);
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
        $this->authorize('update', $client);

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
     * Update the client notes.
     *
     * @param Request $request
     * @param Client $client
     * @param ClientNarrative $narrative
     * @return SuccessResponse
     */
    public function update(Request $request, Client $client, ClientNarrative $narrative)
    {
        $this->authorize('update', $client);

        $request->validate([
            'notes' => 'required|min:1|max:63000',
        ]);

        $narrative->update(['notes' => $request->notes]);
        
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
        $this->authorize('update', $client);

        if ($narrative->delete()) {
            return new SuccessResponse('Narrative notes were successfully deleted.');
        }   
        
        return new ErrorResponse(500, 'An unexpected error occurred, please try again.');
    }
}