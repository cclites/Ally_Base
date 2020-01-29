<?php

namespace App\Services;

use mikehaertl\pdftk\Pdf;

class TaxDocumentPrinter
{
    /**
     * Create Pdf object of filled out 1099 MISC Copy B form
     * used to send to Caregivers.
     *
     * @param int $year
     * @param string $payerName
     * @param string $payerAddress1
     * @param string|null $payerAddress2
     * @param string $payerCity
     * @param string $payerState
     * @param string $payerZip
     * @param string $payerTin
     * @param string $recipientTin
     * @param string $recipientName
     * @param string $recipientAddress1
     * @param string|null $recipientAddress2
     * @param string $recipientCity
     * @param string $recipientState
     * @param string $recipientZip
     * @param float $amount
     * @param bool $maskPayerTin
     * @param bool $maskRecipientTin
     * @return Pdf
     */
    public function create1099MiscCopyB(
        int $year,
        string $payerName,
        string $payerAddress1,
        ?string $payerAddress2,
        string $payerCity,
        string $payerState,
        string $payerZip,
        string $payerTin,
        string $recipientTin,
        string $recipientName,
        string $recipientAddress1,
        ?string $recipientAddress2,
        string $recipientCity,
        string $recipientState,
        string $recipientZip,
        float $amount,
        bool $maskPayerTin = true,
        bool $maskRecipientTin = true
    )
    {
        $pdf = new Pdf("../resources/pdf_forms/caregiver1099s/$year/1099-misc-copy-b.pdf");

        if ($maskPayerTin) {
            $payerTin = '**-*******';
        }

        if ($maskRecipientTin) {
            $recipientTin = '***-**-****'; //'***-**-' . substr($recipientTin . "", -4);
        }

        $pdf->fillForm([
            /** COPY B **/
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_1[0]' => strtoupper("$payerName\n$payerAddress1\n$payerAddress2\n$payerCity, $payerState $payerZip"), // payer name and address
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_2[0]' => $payerTin, //payers tin
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_3[0]' => $recipientTin, //recipient tin
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_4[0]' => strtoupper($recipientName), //recipient name
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_5[0]' => strtoupper("$recipientAddress1 $recipientAddress2"), //recipient street address
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_6[0]' => strtoupper("$recipientCity, $recipientState $recipientZip"), //recipient city, state, zip
            'topmostSubform[0].CopyB[0].RightCol[0].f2_14[0]' => $amount, // non-employee compensation

            /** COPY 2 **/
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_1[0]' => strtoupper("$payerName\n$payerAddress1\n$payerAddress2\n$payerCity, $payerState $payerZip"), // payer name and address
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_2[0]' => $payerTin, //payers tin
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_3[0]' => $recipientTin, //recipient tin
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_4[0]' => strtoupper($recipientName), //recipient name
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_5[0]' => strtoupper("$recipientAddress1 $recipientAddress2"), //recipient street address
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_6[0]' => strtoupper("$recipientCity, $recipientState $recipientZip"), //recipient city, state, zip
            'topmostSubform[0].Copy2[0].RightColumn[0].f2_14[0]' => $amount, // non-employee compensation
        ])->execute();

        return $pdf;
    }

    /**
     * Create Pdf object of filled out 1099 MISC Copy C form
     * used to send to Clients.
     *
     * @param int $year
     * @param string $payerName
     * @param string $payerAddress1
     * @param string|null $payerAddress2
     * @param string $payerCity
     * @param string $payerState
     * @param string $payerZip
     * @param string $payerTin
     * @param string $recipientTin
     * @param string $recipientName
     * @param string $recipientAddress1
     * @param string|null $recipientAddress2
     * @param string $recipientCity
     * @param string $recipientState
     * @param string $recipientZip
     * @param float $amount
     * @param bool $maskPayerTin
     * @param bool $maskRecipientTin
     * @return Pdf
     */
    public function create1099MiscCopyC(
        int $year,
        string $payerName,
        string $payerAddress1,
        ?string $payerAddress2,
        string $payerCity,
        string $payerState,
        string $payerZip,
        string $payerTin,
        string $recipientTin,
        string $recipientName,
        string $recipientAddress1,
        ?string $recipientAddress2,
        string $recipientCity,
        string $recipientState,
        string $recipientZip,
        float $amount,
        bool $maskPayerTin = true,
        bool $maskRecipientTin = true
    )
    {
        $pdf = new Pdf("../resources/pdf_forms/caregiver1099s/$year/1099-misc-copy-c.pdf");

        if ($maskPayerTin) {
            $payerTin = '**-*******';
        }

        if ($maskRecipientTin) {
            $recipientTin = '***-**-****'; //'***-**-' . substr($recipientTin . "", -4);
        }

        $pdf->fillForm([
            /** COPY C **/
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_1[0]' => strtoupper("$payerName\n$payerAddress1\n$payerAddress2\n$payerCity, $payerState $payerZip"), // payer name and address
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_2[0]' => $payerTin,
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_3[0]' => $recipientTin, //recipient tin
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_4[0]' => strtoupper($recipientName), //recipient name
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_5[0]' => strtoupper("$recipientAddress1 $recipientAddress2"), //recipient street address
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_6[0]' => strtoupper("$recipientCity, $recipientState $recipientZip"), //recipient city, state, zip
            'topmostSubform[0].CopyC[0].RightColumn[0].f2_14[0]' => $amount,
        ])->execute();

        return $pdf;
    }
}