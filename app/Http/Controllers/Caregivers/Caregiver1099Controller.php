<?php

namespace App\Http\Controllers\Caregivers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use App\Caregiver1099Payer;
use App\Caregiver1099;

class Caregiver1099Controller extends Controller
{
    /**
     * Display a listing of the resource for a single caregiver
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /** @var \App\Caregiver $caregiver */
        $caregiver = auth()->user()->role;

        $data = $caregiver->caregiver1099s()
            ->with('client')
            ->where('caregiver_1099_payer', '<>', Caregiver1099Payer::ALLY())
            ->get()
            ->map(function (Caregiver1099 $item) {
                return [
                    'id' => $item->id,
                    'year' => $item->year,
                    'payer' => $item->client_first_name . ' ' . $item->client_last_name,
                    'client' => $item->client->name,
                ];
            });

        // If there is an Ally payer 1099, just show a single record
        // because they will get aggregated on print.
        $ally1099s = $caregiver->caregiver1099s()
            ->with('client')
            ->where('caregiver_1099_payer', Caregiver1099Payer::ALLY())
            ->get();

        if ($ally1099s->count() > 0) {
            $ally1099s->groupBy('year')
                ->each(function (Collection $group) use ($data) {
                    $data[] = [
                        'id' => $group[0]->id,
                        'year' => $group[0]->year,
                        'payer' => 'Ally',
                        'client' => join(', ', $group->map(function ($item) {
                            return $item->client->name;
                        })->toArray())
                    ];
                });
        }

        return response()->json($data->values());
    }

    /**
     * Generate Caregiver 1099 PDF and download it.
     *
     * @param Caregiver1099 $caregiver1099
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function downloadPdf(Caregiver1099 $caregiver1099)
    {
        $this->authorize('read', $caregiver1099);

        $document = $caregiver1099->getFilledCaregiverPdf(false, false);

        if ($caregiver1099->isFromAlly()) {
            $filename = "Ally - {$caregiver1099->caregiver_first_name} {$caregiver1099->caregiver_last_name} {$caregiver1099->year} 1099.pdf";
        } else {
            $filename = "{$caregiver1099->client_first_name} {$caregiver1099->client_last_name} - {$caregiver1099->caregiver_first_name} {$caregiver1099->caregiver_last_name} {$caregiver1099->year} 1099.pdf";
        }

        $document->send($filename);
    }
}
