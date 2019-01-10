<?php

namespace Tests\Unit;

use App\Billing\ClientPayer;
use App\Billing\Contracts\InvoiceableInterface;
use App\Billing\Exceptions\InvalidClientPayers;
use App\Billing\Generators\BaseInvoiceGenerator;
use App\Billing\Generators\ClientInvoiceGenerator;
use App\Billing\Validators\ClientPayerValidator;
use App\Client;
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

    protected function setUp()
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
        $invoiceable = \Mockery::mock(InvoiceableInterface::class);
        $invoiceable->shouldReceive('getItemsForPayment')->with($this->client);
        BaseInvoiceGenerator::$invoiceables = ['mock' => $invoiceable];

        $this->invoicer()->getInvoiceables($this->client);
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
        $invoiceable->shouldReceive('getItemGroup')->andReturn($group);
        $invoiceable->shouldReceive('getItemUnits')->andReturn($units);
        $invoiceable->shouldReceive('getItemDate')->andReturn($date);
        $invoiceable->shouldReceive('getClientRate')->andReturn($rate);
        $invoiceable->shouldReceive('getAmountDue')->andReturn($total);

        $data = $this->invoicer()->getItemData($invoiceable);
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
    public function get_payer_allowance_should_return_a_float()
    {
        $mockPayer = \Mockery::mock(ClientPayer::class);
        $this->assertInternalType('float', $this->invoicer()->getPayerAllowance($mockPayer));
    }
}
