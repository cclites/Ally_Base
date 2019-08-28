<?php

namespace App\Http\Controllers\Admin\Reports;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Client;
use App\Caregiver;
use App\Rules\ValidSSN;
use Crypt;

class AdminBadSsnReportController extends Controller
{
    /**
     * Get the Bad SSN Report.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $label = '';

        $report = collect();
        if ($request->input('type') === 'clients') {
            $label = "Client";
            $report = Client::with('user')->whereNotNull('ssn')->get()
                ->map(function (Client $client) {
                    try {
                        if ($this->validSSN($client->ssn)) {
                            return null;
                        }
                    } catch (DecryptException $ex) {
                        // Corrupt -> continue
                    }

                    return [
                        'id' => $client->id,
                        'name' => $client->nameLastFirst(),
                        'business' => $client->business->name,
                        'type' => 'client',
                    ];
                })
                ->filter()
                ->values();
        } elseif ($request->input('type') == 'caregivers') {
            $label = "Caregiver";
            $report = Caregiver::with('user')->whereNotNull('ssn')->get()
                ->map(function (Caregiver $caregiver) {
                    try {
                        if ($this->validSSN($caregiver->ssn)) {
                            return null;
                        }
                    } catch (DecryptException $ex) {
                        // Corrupt -> continue
                    }

                    return [
                        'id' => $caregiver->id,
                        'name' => $caregiver->nameLastFirst(),
                        'business' => optional($caregiver->businesses->first())->name,
                        'type' => 'caregiver',
                    ];
                })
                ->filter()
                ->values();
        }

        return view_component(
            'bad-ssn-report',
            'Bad ' . $label . ' SSNs Report',
            ['report' => $report],
            [
                'Home' => route('home'),
                'Reports' => route('admin.reports.index')
            ]
        );
    }

    /**
     * Check for valid SSN format.
     *
     * @param null|string $ssn
     * @return bool
     */
    private function validSSN(?string $ssn) : bool
    {
        if (empty($ssn)) {
            return true;
        }

        return preg_match('/(\d{3}|\*{3})-(\d{2}|\*{2})-(\d{4}|\*{4})/', $ssn);
    }
}