<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Nacha\Field\TransactionCode;
use Nacha\File;
use Nacha\Batch;
use Nacha\Record\DebitEntry;
use Nacha\Record\CcdEntry;
use Nacha\Record\Addenda;

class NachaAchController extends Controller
{
    private $file;

    public function index() {
        return view('admin.nachaach.index');
    }

    public function generate(Request $request) {
        $this->setup($request->all());
        $batch = $this->getBatch($request->all());


        $this->file->addBatch($batch);
        $output = (string)$this->file;

        if(!empty($output)) {
            $data = [ 'data' => $output ];
            $code = 200;
        } else {
            $data = [ 'message' => 'Something went wrong' ];
            $code = 500;
        }

        return response()->json($data, $code);
    }

    private function setup($data) {
        $this->file = new File();
        $this->file->getHeader()
            ->setPriorityCode('01')
            ->setImmediateDestination($data['fh_immediate_destination'])
            ->setImmediateOrigin($data['fh_immediate_origin'])
            ->setFileCreationDate(date('ymd'))
            ->setFileCreationTime(date('Hi'))
            ->setFileIdModifier('A')
            ->setRecordSize('094')
            ->setBlockingFactor('10')
            ->setFormatCode('1')
            ->setImmediateDestinationName($data['fh_immediate_destination_name'])
            ->setImmediateOriginName($data['fh_immediate_origin_name'])
            ->setReferenceCode('ACH');
    }

    private function getBatch($data, $addendum = true) {
        $batch = new Batch();
        $batch->getHeader()
            ->setServiceClassCode($data['bh_service_class_code'])
            ->setCompanyName($data['bh_company_name'])
            ->setCompanyDiscretionaryData('')
            ->setCompanyId($data['fh_immediate_origin'])
            ->setStandardEntryClassCode('PPD')
            ->setCompanyEntryDescription($data['bh_company_entry_description'])
            ->setCompanyDescriptiveDate(date('ymd'))
            ->setEffectiveEntryDate(date('ymd'))
            ->setOriginatorStatusCode('1')
            ->setOriginatingDFiId($data['bh_originating_DFI_ID'])
            ->setBatchNumber('');

        if(isset($data['details']) && !empty($data['details'])) {
            foreach($data['details'] as $detail) {
                $entry = (new DebitEntry)
                    ->setTransactionCode(TransactionCode::CHECKING_DEBIT)
                    ->setReceivingDfiId(substr($data['fh_immediate_destination'], 0, 8))
                    ->setCheckDigit(substr($data['fh_immediate_destination'], -1))
                    ->setDFiAccountNumber($detail['ppded_DFI_account_number'])
                    ->setAmount($detail['ppded_amount'])
                    ->setIndividualId($detail['ppded_individual_identification_number'])
                    ->setDiscretionaryData('')
                    ->setIdividualName($detail['ppded_individual_name'])
                    ->setAddendaRecordIndicator(1)
                    ->setTraceNumber('99936340', 1);

                if ($addendum) {
                    $entry->addAddenda((new Addenda)
                        ->setPaymentRelatedInformation(''));
                }

                $batch->addEntry($entry);
            }

        }

        return $batch;
    }
}
