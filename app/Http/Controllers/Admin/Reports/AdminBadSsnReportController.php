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
    public function index(Request $request, $type)
    {
        $label = ucfirst($type);

        if($request->json){

            $report = collect();
            if ($type === 'clients') {
                $report = Client::query()->with(['user']);

                if($request->businesses){
                    $report->forRequestedBusinesses();
                }

                if($request->client_types){
                    $clientTypes = explode(',', $request->client_types);
                    $report->whereIn('client_type', $clientTypes);
                }

                $data = $report->get()
                    ->map(function (Client $client) {

                        try {
                            if ($client->ssn && $this->validSSN($client->ssn)) {
                                return null;
                            }
                        } catch (DecryptException $ex) {
                            // Corrupt -> continue
                        }

                        return [
                            'id' => $client->id,
                            'name' => $client->nameLastFirst(),
                            'business' => $client->business->name,
                            'email' => $client->email,
                            'type' => 'client',
                        ];
                    })
                    ->filter()
                    ->values();
            } elseif ($type == 'caregivers') {
                $report = Caregiver::query()->with(['user']);

                if($request->businesses){
                    $report->forRequestedBusinesses();
                }

                $data = $report->get()
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
                            'email' => $caregiver->email,
                            'type' => 'caregiver',
                        ];
                    })
                    ->filter()
                    ->values();
            }

            if($request->csv){
                return $this->toCsv($data);
            }

            return response()->json($data);

        }

        return view_component(
            'bad-ssn-report',
            'Bad SSNs Report For ' . $label,
            ['type' => $type],
            [
                'Home' => route('home'),
                '1099' => route('admin.admin-1099-actions')
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
        return preg_match('/(\d{3}|\*{3})-(\d{2}|\*{2})-(\d{4}|\*{4})/', $ssn);
    }

    /**
     * @param $data
     * @return string
     */
    private function toCsv($rows){

        if (empty($rows)) {
            return '';
        }

        // Build header
        $headerRow = collect($rows[0])
            ->keys()
            ->map(function ($key) {
                return $key === 'id' ? 'ID' : ucwords(str_replace('_', ' ', $key));
            })
            ->toArray();

        // Build rows
        $csv[] = '"' . implode('","', $headerRow) . '"';
        foreach ($rows as $row) {
            $csv[] = '"' . implode('","', $row) . '"';
        }

        $report = implode("\r\n", $csv);

        return \Response::make($report, 200, [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Payroll-Export-Report.csv"',
        ]);
    }
}