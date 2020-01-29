<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Caregiver1099Payer;
use App\Caregiver1099;

class Caregiver1099Controller extends Controller
{
    /**
     * Display a listing of the resource for a single caregiver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        /** @var \App\Client $client */
        $client = auth()->user()->role;

        $data = $client->caregiver1099s()
            ->with('caregiver')
            ->where('caregiver_1099_payer', '<>', Caregiver1099Payer::ALLY())
            ->get()
            ->map(function (Caregiver1099 $item) {
                return [
                    'id' => $item->id,
                    'year' => $item->year,
                    'caregiver' => $item->caregiver->nameLastFirst,
                ];
            });

        return response()->json($data->values());
    }

    /**
     * Generate Client 1099 PDF and download it.
     *
     * @param Caregiver1099 $caregiver1099
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function downloadPdf(Caregiver1099 $caregiver1099)
    {
        $this->authorize('read', $caregiver1099);

        $document = $caregiver1099->getFilledClientPdf(false, false);

        $filename = "{$caregiver1099->client_first_name} {$caregiver1099->client_last_name} - {$caregiver1099->caregiver_first_name} {$caregiver1099->caregiver_last_name} {$caregiver1099->year} 1099.pdf";

        $document->send($filename);
    }
}
