<?php

namespace App\Http\Controllers\Admin;

use App\Caregiver1099;
use App\Caregiver;
use App\Caregiver1099Payer;
use App\CaregiverYearlyEarnings;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Rules\ValidSSN;
use App\Http\Requests\StoreCaregiver1099Request;
use App\Http\Requests\UpdateCaregiver1099Request;
use App\Http\Requests\Transmit1099Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Caregiver1099Controller extends Controller
{
    protected $headerRow = [
        'Table Id',
        'Created At',
        'Void (Enter 0 or 1)',
        'Corrected (Enter 0 or 1)',
        'Payer Name',
        'Payer Address',
        'Payer City',
        'Payer State',
        'Payer Zip',
        'Payer Phone',
        'Payer TIN',
        'Recipient TIN',
        'Recipient Name',
        'Recipient Address',
        'Recipient City',
        'Recipient State',
        'Recipient Zip',
        'Acct No',
        '2nd TIN Notice',
        'Box 15A',
        'Box 15B',
        'Box 1 Rents',
        'Box 2 Royalties',
        'Box 3 Other Income',
        'Box 4 Tax Witheld',
        'Box 5',
        'Box 6',
        'Box 7',
        'Box 8',
        'Box 9',
        'Box 10',
        'Box 13',
        'Box 14',
        'Box 16_1',
        'Box 16_2',
        'Box 17_1',
        'Box 17_2',
        'Box 18_1',
        'Box 18_2'
    ];

    /**
     * Display a listing of the resource for a single caregiver
     *
     * @return Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Caregiver $caregiver)
    {
        $caregiver_1099s = $caregiver->caregiver1099s->map(function ($caregiver_1099) {
            return [
                'year' => $caregiver_1099->year,
                'name' => $caregiver_1099->client_first_name . " " . $caregiver_1099->client_last_name,
                'id' => $caregiver_1099->id
            ];
        })
            ->groupBy('year');

        return response()->json($caregiver_1099s);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCaregiver1099Request $request
     * @return ErrorResponse|SuccessResponse
     */
    public function store(StoreCaregiver1099Request $request)
    {
        /** @var \App\CaregiverYearlyEarnings $earnings */
        $earnings = CaregiverYearlyEarnings::where('business_id', $request->business_id)
            ->where('client_id', $request->client_id)
            ->where('caregiver_id', $request->caregiver_id)
            ->where('year', $request->year)
            ->first();

        if (empty($earnings)) {
            return new ErrorResponse(500, 'Could not find earnings data for this caregiver and client.');
        }

        if ($errors = $earnings->getMissing1099Errors()) {
            $errors = implode(',', $errors);
            return new ErrorResponse(500, "Could not create 1099 because of missing data.  Please fix the following: $errors.");
        }

        $record = $earnings->make1099Record();
        $record->save();

        return new SuccessResponse('Caregiver 1099 created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Caregiver1099 $caregiver1099
     * @return Response
     */
    public function edit(Caregiver1099 $caregiver1099)
    {
        //decode the ssns
        $decodedClientSsn = decrypt($caregiver1099->client_ssn);
        $decodedCaregiverSsn = decrypt($caregiver1099->caregiver_ssn);

        $caregiver1099->client_ssn = "***-**-" . substr($decodedClientSsn, -4);
        $caregiver1099->caregiver_ssn = "***-**-" . substr($decodedCaregiverSsn, -4);

        return response()->json($caregiver1099);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return Response
     */
    public function update(UpdateCaregiver1099Request $request, Caregiver1099 $caregiver1099)
    {
        $caregiver1099->fill($request->validated());

        if (strpos($caregiver1099->client_ssn, "#") !== false) {
            $this->validate($request, [
                'client_ssn' => ['required', new ValidSSN(false)],
            ]);
            $caregiver1099->client_ssn = encrypt($caregiver1099->client_ssn);
        } else {
            unset($caregiver1099->client_ssn);
        }

        if (strpos($caregiver1099->caregiver_ssn, "#") !== false) {
            $this->validate($request, [
                'caregiver_ssn' => ['required', new ValidSSN(false)],
            ]);
            $caregiver1099->caregiver_ssn = encrypt($caregiver1099->caregiver_ssn);
        } else {
            unset($caregiver1099->caregiver_ssn);
        }

        if ($caregiver1099->save()) {
            return new SuccessResponse("Caregiver 1099 has been updated");
        }

        return new ErrorResponse("Unable to update Caregiver 1099");
    }

    /**
     * Creates a csv file of 1099s for transmission
     *
     * @param Request $request
     * @param Caregiver1099 $caregiver1099
     * @param $year
     * @return Response
     */
    public function transmit(Request $request, Caregiver1099 $caregiver1099, $year)
    {
        $maskRecipientSSN = $request->mask == 1;

        $systemSettings = \DB::table('system_settings')->first();

        $caregiver1099s = $caregiver1099
            ->where('year', $year)
            ->whereNull('transmitted_at')
            ->with(['client', 'client.user'])
            ->get()
            ->map(function ($cg1099) use ($systemSettings, $maskRecipientSSN) {
//                $cg1099->update(['transmitted_at'=>\Carbon\Carbon::now(),'transmitted_by'=> auth()->user()->id]);

                $payerTin = $this->ensureSsnFormat($cg1099->client_ssn ? decrypt($cg1099->client_ssn) : '');
                $payerName = $cg1099->client_first_name . " " . $cg1099->client_last_name;
                $payerAddress = $cg1099->client_address1 . ($cg1099->client_address2 ? ", " . $cg1099->client_address2 : '');
                $payerCity = $cg1099->client_city;
                $payerState = $cg1099->client_state;
                $payerZip = $cg1099->client_zip;
                $payerPhone = $cg1099->client->user->getDefaultPhoneAttribute();
                $caregiverTin = decrypt($cg1099->caregiver_ssn);

                if ($maskRecipientSSN) {
                    $caregiverTin = '***-**-' . substr($caregiverTin, strlen($caregiverTin) - 4, 4);
                } else {
                    $caregiverTin = $this->ensureSsnFormat($caregiverTin);
                }

                if ($cg1099->caregiver_1099_payer == Caregiver1099Payer::ALLY()) {
                    $payerName = $systemSettings->company_name;
                    $payerTin = $systemSettings->company_ein;
                    $payerPhone = $systemSettings->company_contact_phone;
                }

                return [
                    'payer_name' => strtoupper($payerName),
                    'payer_address' => strtoupper($payerAddress),
                    'payer_city' => strtoupper($payerCity),
                    'payer_state' => strtoupper($payerState),
                    'payer_zip' => $payerZip,
                    'payer_phone' => $payerPhone,
                    'payer_tin' => $payerTin,
                    'recipient_tin' => $caregiverTin,
                    'recipient_name' => strtoupper($cg1099->caregiver_first_name . " " . $cg1099->caregiver_last_name),
                    'recipient_address' => strtoupper($cg1099->caregiver_address1 . "\n" . filled($cg1099->caregiver_address2)),
                    'recipient_city' => strtoupper($cg1099->caregiver_city),
                    'recipient_state' => strtoupper($cg1099->caregiver_state),
                    'recipient_zip' => $cg1099->caregiver_zip,
                    'payment_total' => $cg1099->payment_total,
                    'created_at' => $cg1099->created_at->setTimezone(config('ally.local_timezone'))->toDateString(),
                    'id' => $cg1099->id,
                ];
            });

        $csv = $this->toCsv($caregiver1099s);

        return \Response::make(json_encode($csv), 200, [
            'Content-type' => 'application/csv',
            'Content-Disposition' => 'attachment; filename="Transmission.csv"',
        ]);

    }

    public function ensureSsnFormat(string $ssn) : string
    {
        if (strlen($ssn) < 9) {
            return '';
        }

        $ssn = str_replace('-', '', $ssn);
        return substr($ssn, 0, 3) . '-' . substr($ssn, 3, 2) . '-' . substr($ssn, 5, 4);
    }

    /**
     * Convert 1099 data to CSV
     *
     * @param $data
     * @return string
     */
    private function toCsv($rows)
    {
        if (count($rows) < 1) {
            return '';
        }

        // Add header
        $csv[] = '"' . implode('","', $this->headerRow) . '"';

        // build rows
        foreach ($rows as $row) {

            $data = [
                $row['id'],
                $row['created_at'],
                0,
                0,
                $row['payer_name'],
                $row['payer_address'],
                $row['payer_city'],
                $row['payer_state'],
                $row['payer_zip'],
                $row['payer_phone'],
                $row['payer_tin'],
                $row['recipient_tin'],
                $row['recipient_name'],
                $row['recipient_address'],
                $row['recipient_city'],
                $row['recipient_state'],
                $row['recipient_zip'],
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                $row['payment_total'],
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
            ];

            $csv[] = '"' . implode('","', $data) . '"';
        }

        $imploded = implode("\r\n", $csv);

        //remove comment character from data and return
        return str_replace("#", "", $imploded);
    }

    /**
     * Generate PDF on the fly and download it.
     *
     * @param Caregiver1099 $caregiver1099
     */
    public function downloadPdf(Caregiver1099 $caregiver1099)
    {
        // TODO: Unmask payer and caregiver SSN
        $pdf = $caregiver1099->getFilledCaregiverPdf(true, true);
        $fileName = $caregiver1099->client_first_name . " " . $caregiver1099->client_last_name . '_' . $caregiver1099->caregiver_first_name . "_" . $caregiver1099->caregiver_last_name . '1099.pdf';
        $pdf->send($fileName);
    }

    public function admin()
    {
        $years = \DB::table('caregiver_1099s')->distinct()->pluck('year');

        return view_component(
            'caregiver-1099-admin',
            'Admin 1099',
            [
                'years' => $years
            ],
            [
                'Home' => route('home'),
                '1099' => route('admin.admin-1099-actions')
            ]
        );
    }
}