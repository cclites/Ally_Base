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
        $this->setup();
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

    private function setup() {
        $this->file = new File();
        $this->file->getHeader()->setPriorityCode(1)
            ->setImmediateDestination('051000033')
            ->setImmediateOrigin('059999997')
            ->setFileCreationDate('060210')
            ->setFileCreationTime('2232')
            ->setFormatCode('1')
            ->setImmediateDestinationName('ImdDest Name')
            ->setImmediateOriginName('ImdOriginName')
            ->setReferenceCode('Reference');
    }

    private function getBatch($data, $addendum = true) {
        $batch = new Batch();
        $batch->getHeader()
            ->setCompanyName('TESTING')
            ->setCompanyDiscretionaryData('INCLUDES OVERTIME')
            ->setCompanyId('1419871234')
            ->setStandardEntryClassCode('PPD')
            ->setCompanyEntryDescription('PAYROLL')
            ->setCompanyDescriptiveDate('0602')
            ->setEffectiveEntryDate('0112')
            ->setOriginatorStatusCode('1')
            ->setOriginatingDFiId('01021234');

        $entry = (new DebitEntry)
            ->setTransactionCode(TransactionCode::CHECKING_DEBIT)
            ->setReceivingDfiId('09101298')
            ->setCheckDigit(7)
            ->setDFiAccountNumber($data['account'])
            ->setAmount($data['amount'])
            ->setIndividualId('test id')
            ->setIdividualName('Test name')
            ->setDiscretionaryData('S')
            ->setAddendaRecordIndicator(0)
            ->setTraceNumber('99936340', 1);

        if ($addendum) {
            $entry->addAddenda((new Addenda)
                ->setPaymentRelatedInformation(''));
        }

        $batch->addEntry($entry);
        return $batch;
    }
}
