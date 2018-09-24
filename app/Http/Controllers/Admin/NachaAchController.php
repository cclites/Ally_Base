<?php

namespace App\Http\Controllers\Admin;

use ClassesWithParents\D;
use Validator;
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
        $request->validate([
            'fh_immediate_destination' => 'required|digits:9|alpha_num',
            'fh_immediate_origin' => 'required|digits:9|alpha_num',
            'fh_immediate_destination_name' => 'required',
            'fh_immediate_origin_name' => 'required',
        ]);

        $data = $request->all();
        $this->createFileHeader($data);
        $this->createBatches($data);

        $output = (string)$this->file;
        $data = [ 'message' => 'Something went wrong' ];
        $code = 500;

        if(!empty($output)) {
            $data = [ 'data' => $output ];
            $code = 200;
        }

        return response()->json($data, $code);
    }

    private function createFileHeader($data) {
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

    private function createBatches($data) {
        if(!empty($data['batches'])) {
            foreach($data['batches'] as $batchData) {
                $batch = new Batch();
                $batch->getHeader()
                    ->setServiceClassCode($batchData['bh_service_class_code'])
                    ->setCompanyName($batchData['bh_company_name'])
                    ->setCompanyDiscretionaryData('')
                    ->setCompanyId($data['fh_immediate_origin'])
                    ->setStandardEntryClassCode('PPD')
                    ->setCompanyEntryDescription($batchData['bh_company_entry_description'])
                    ->setCompanyDescriptiveDate(date('ymd'))
                    ->setEffectiveEntryDate(date('ymd'))
                    ->setOriginatorStatusCode('1')
                    ->setOriginatingDFiId($batchData['bh_originating_DFI_ID'])
                    ->setBatchNumber('');

                if(!empty($batchData['entry_details'])) {
                    foreach($batchData['entry_details'] as $key => $detail) {
                        if($batchData['bh_service_class_code'] == '200') {
                            $batch->addEntry($this->createEntryDetail($detail, '220', $data['fh_immediate_destination'], $key));
                            $batch->addEntry($this->createEntryDetail($detail, '225', $data['fh_immediate_destination'], $key));
                        } else {
                            $batch->addEntry($this->createEntryDetail($detail, $batchData['bh_service_class_code'], $data['fh_immediate_destination'], $key));
                        }
                    }
                }

                $this->file->addBatch($batch);
            }
        }
    }

    private function createEntryDetail($detail, $status, $immediateDestination, $key) {
        $entryType = $status == '220' ? new CcdEntry() : new DebitEntry();
        $transactionCode = $status == '220' ? TransactionCode::CHECKING_DEPOSIT : TransactionCode::CHECKING_DEBIT;
        $entry = ($entryType)
            ->setTransactionCode($transactionCode)
            ->setReceivingDfiId(substr($immediateDestination, 0, 8))
            ->setCheckDigit(substr($immediateDestination, -1))
            ->setAmount($detail['ppded_amount']);

        if($status == '220') {
            $entry->setReceivingDFiAccountNumber($detail['ppded_DFI_account_number'])
                ->setReceivingCompanyId($detail['ppded_individual_identification_number'])
                ->setReceivingCompanyName($detail['ppded_individual_name']);

        } else {
            $entry->setDFiAccountNumber($detail['ppded_DFI_account_number'])
                ->setIndividualId($detail['ppded_individual_identification_number'])
                ->setIdividualName($detail['ppded_individual_name']);
        }

        $entry->setDiscretionaryData('')
            ->setAddendaRecordIndicator(1)
            ->setTraceNumber(substr($immediateDestination, 0, 7) . '0', $key);

        return $entry;
    }
}
