<?php

namespace Tests\Unit;

use App\Billing\ClientPayer;
use App\Billing\Contracts\InvoiceableInterface;
use App\Billing\Exceptions\InvalidClientPayers;
use App\Billing\Generators\BaseInvoiceGenerator;
use App\Billing\Generators\ClientInvoiceGenerator;
use App\Billing\Validators\ClientPayerValidator;
use App\Client;
use Carbon\Carbon;
use Mockery\Mock;
use Tests\TestCase;

class ClientInvoiceGeneratorTest extends TestCase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var \Mockery\Mock
     */
    private $validator;

    protected function setUp() : void
    {
        parent::setUp();
        $this->client = new Client();
    }

    protected function validateReturns(bool $bool)
    {
        $this->validator = \Mockery::mock(ClientPayerValidator::class);
        $this->validator->shouldReceive('validate')
            ->with($this->client)
            ->andReturn($bool);
    }

    protected function invoicer()
    {
        if (!$this->validator) $this->validateReturns(true);
        return new ClientInvoiceGenerator($this->validator);
    }

    /**
     * @test
     */
    public function no_invoices_should_be_generated_for_a_client_with_invalid_payers()
    {
        $this->validateReturns(false);

        $this->expectException(InvalidClientPayers::class);
        $this->invoicer()->generateAll($this->client);
    }

    /**
     * @test
     */
    public function invoiceables_are_collected_from_their_classes()
    {
        $date = Carbon::now();
        $invoiceable = \Mockery::mock(InvoiceableInterface::class);
        $invoiceable->shouldReceive('getItemsForPayment')->with($this->client, $date)->andReturn(collect());
        BaseInvoiceGenerator::$invoiceables = ['mock' => $invoiceable];

        $this->invoicer()->getInvoiceables($this->client, $date);
    }

    /**
     * @test
     */
    public function invoiceables_provide_the_correct_item_data()
    {
        $invoiceable = \Mockery::mock(InvoiceableInterface::class);
        $name = "Hello World";
        $group = "Group";
        $units = 1.25;
        $date = '2019-01-01 12:00:00';
        $rate = 12.50;
        $total = 15.63;

        $invoiceable->shouldReceive('getItemName')->andReturn($name);
        $invoiceable->shouldReceive('getItemNotes')->andReturn('');
        $invoiceable->shouldReceive('getItemGroup')->andReturn($group);
        $invoiceable->shouldReceive('getItemUnits')->andReturn($units);
        $invoiceable->shouldReceive('getItemDate')->andReturn($date);
        $invoiceable->shouldReceive('getClientRate')->andReturn($rate);
        $invoiceable->shouldReceive('getAmountDue')->andReturn($total);
        $invoiceable->shouldReceive('hasFeeIncluded')->andReturn(true);

        $data = $this->invoicer()->getItemData($invoiceable, $rate, $total);
        $this->assertSame($name, $data['name']);
        $this->assertSame($group, $data['group']);
        $this->assertSame($units, $data['units']);
        $this->assertSame($date, $data['date']);
        $this->assertSame($rate, $data['rate']);
        $this->assertSame($total, $data['total']);
        $this->assertSame($total, $data['amount_due']);
    }

    /**
     * @test
     */
    function invoiceables_should_be_sorted_by_payer_then_date()
    {
        $invoiceableFirst = \Mockery::mock(InvoiceableInterface::class);
        $invoiceableFirst->shouldReceive('getClientRate')->andReturn(10.00);
        $invoiceableFirst->shouldReceive('getPayerId')->andReturn(1);
        $invoiceableFirst->shouldReceive('getItemDate')->andReturn('2019-01-31');

        $invoiceableSecond = \Mockery::mock(InvoiceableInterface::class);
        $invoiceableSecond->shouldReceive('getClientRate')->andReturn(10.00);
        $invoiceableSecond->shouldReceive('getPayerId')->andReturn(null);
        $invoiceableSecond->shouldReceive('getItemDate')->andReturn('2019-01-18');

        $invoiceableThird = \Mockery::mock(InvoiceableInterface::class);
        $invoiceableThird->shouldReceive('getClientRate')->andReturn(10.00);
        $invoiceableThird->shouldReceive('getPayerId')->andReturn(null);
        $invoiceableThird->shouldReceive('getItemDate')->andReturn('2019-01-19');

        $invoiceables = [$invoiceableThird, $invoiceableFirst, $invoiceableSecond]; // unordered
        $sorted = $this->invoicer()->sortInvoiceables($invoiceables);

        $this->assertSame($invoiceableFirst, $sorted[0], 'The specific payer invoiceable was not first.');
        $this->assertSame($invoiceableSecond, $sorted[1], 'The auto payer invoiceables were not sorted properly.');
    }

    /**
     * @test
     */
    function payers_should_be_sorted_by_type_then_priority()
    {
        // Allowance payers, then split payers, then balance payers by priority
        $payerFirst = new ClientPayer([
            'payment_allocation' => ClientPayer::ALLOCATION_MONTHLY,
            'priority' => 1,
        ]);

        $payerSecond = new ClientPayer([
            'payment_allocation' => ClientPayer::ALLOCATION_WEEKLY,
            'priority' => 4,
        ]);

        $payerThird = new ClientPayer([
            'payment_allocation' => ClientPayer::ALLOCATION_SPLIT,
            'priority' => 2,
        ]);

        $payerFourth = new ClientPayer([
            'payment_allocation' => ClientPayer::ALLOCATION_SPLIT,
            'priority' => 5,
        ]);

        $payerFifth = new ClientPayer([
            'payment_allocation' => ClientPayer::ALLOCATION_BALANCE,
            'priority' => 10,
        ]);

        $payers = [$payerFifth, $payerThird, $payerFourth, $payerSecond, $payerFirst]; // unordered
        $sorted = $this->invoicer()->sortPayers($payers);

        $this->assertSame($payerFirst, $sorted[0], 'The allowance payers were not sorted properly.');
        $this->assertSame($payerThird, $sorted[2], 'The higher priority split payer was not sorted third.');
        $this->assertSame($payerFourth, $sorted[3], 'The lower priority split payer was not sorted fourth.');
        $this->assertSame($payerFifth, $sorted[4], 'The balance payer was not sorted last.');

    }

}
