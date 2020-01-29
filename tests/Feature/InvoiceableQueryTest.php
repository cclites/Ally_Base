<?php

namespace Tests\Feature;

use App\Billing\BusinessInvoice;
use App\Billing\BusinessInvoiceItem;
use App\Billing\CaregiverInvoice;
use App\Billing\CaregiverInvoiceItem;
use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\Invoiceable\InvoiceableModel;
use App\Billing\Queries\InvoiceableQuery;
use App\Shift;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceableQueryTest extends TestCase
{
    use RefreshDatabase;

    /** @var string */
    private $invoiceableClass;

    /** @var InvoiceableQuery */
    private $query;

    protected function setUp() : void
    {
        parent::setUp();
        $this->invoiceableClass = Shift::class;
        $this->query = new InvoiceableQuery(new $this->invoiceableClass);
    }

    /**
     * @test
     */
    public function query_can_get_all_invoiceables()
    {
        $this->createInvoiceables(3);
        $this->assertCount(3, $this->query->get());
    }

    /**
     * @test
     */
    public function query_can_get_models_without_a_client_invoice()
    {
        $without = $this->createInvoiceable();
        $with = $this->createInvoiceable();
        $this->createClientInvoice($with);

        $results = $this->query->doesntHaveClientInvoice()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($without->getKey(), $results[0]->getKey());
    }

    /**
     * @test
     */
    public function query_can_get_models_with_a_client_invoice()
    {
        $without = $this->createInvoiceable();
        $with = $this->createInvoiceable();
        $this->createClientInvoice($with);

        $results = $this->query->hasClientInvoice()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($with->getKey(), $results[0]->getKey());
    }

    /**
     * @test
     */
    public function query_can_get_models_with_only_paid_client_invoice()
    {
        $without = $this->createInvoiceable();
        $this->createClientInvoice($without);
        $with = $this->createInvoiceable();
        $this->createClientInvoice($with, true);

        $results = $this->query->hasClientInvoicesPaid()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($with->getKey(), $results[0]->getKey());
    }

    /**
     * @test
     */
    public function query_excludes_invoiceables_when_one_of_the_invoices_is_still_unpaid()
    {
        $without = $this->createInvoiceable();
        $this->createClientInvoice($without);
        $this->createClientInvoice($without, true);

        $results = $this->query->hasClientInvoicesPaid()->get();

        $this->assertCount(0, $results);
    }

    /**
     * @test
     */
    public function query_can_get_models_without_a_caregiver_invoice()
    {
        $without = $this->createInvoiceable();
        $with = $this->createInvoiceable();
        $this->createCaregiverInvoice($with);

        $results = $this->query->doesntHaveCaregiverInvoice()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($without->getKey(), $results[0]->getKey());
    }

    /**
     * @test
     */
    public function query_can_get_models_with_a_caregiver_invoice()
    {
        $without = $this->createInvoiceable();
        $with = $this->createInvoiceable();
        $this->createCaregiverInvoice($with);

        $results = $this->query->hasCaregiverInvoice()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($with->getKey(), $results[0]->getKey());
    }

    /**
     * @test
     */
    public function query_can_get_models_with_only_paid_caregiver_invoice()
    {
        $without = $this->createInvoiceable();
        $this->createCaregiverInvoice($without);
        $with = $this->createInvoiceable();
        $this->createCaregiverInvoice($with, true);

        $results = $this->query->hasCaregiverInvoicesPaid()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($with->getKey(), $results[0]->getKey());
    }

    /**
     * @test
     */
    public function query_can_get_models_without_a_business_invoice()
    {
        $without = $this->createInvoiceable();
        $with = $this->createInvoiceable();
        $this->createBusinessInvoice($with);

        $results = $this->query->doesntHaveBusinessInvoice()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($without->getKey(), $results[0]->getKey());
    }

    /**
     * @test
     */
    public function query_can_get_models_with_a_business_invoice()
    {
        $without = $this->createInvoiceable();
        $with = $this->createInvoiceable();
        $this->createBusinessInvoice($with);

        $results = $this->query->hasBusinessInvoice()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($with->getKey(), $results[0]->getKey());
    }

    /**
     * @test
     */
    public function query_can_get_models_with_only_paid_business_invoice()
    {
        $without = $this->createInvoiceable();
        $this->createBusinessInvoice($without);
        $with = $this->createInvoiceable();
        $this->createBusinessInvoice($with, true);

        $results = $this->query->hasBusinessInvoicesPaid()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($with->getKey(), $results[0]->getKey());
    }

    /**
     * @test
     */
    public function query_can_chain_the_invoice_methods_realistically()
    {
        $without1 = $this->createInvoiceable();

        $without2 = $this->createInvoiceable();
        $this->createCaregiverInvoice($without2);

        $without3 = $this->createInvoiceable();
        $this->createBusinessInvoice($without3);

        $without4 = $this->createInvoiceable();
        $this->createClientInvoice($without4, false);

        $with = $this->createInvoiceable();
        $this->createClientInvoice($with, true);

        $results = $this->query->hasClientInvoicesPaid()
            ->doesntHaveBusinessInvoice()
            ->doesntHaveCaregiverInvoice()
            ->get();

        $this->assertCount(1, $results);
        $this->assertEquals($with->getKey(), $results[0]->getKey());
    }



    protected function createInvoiceable(): InvoiceableModel
    {
        return factory($this->invoiceableClass)->create();
    }

    /**
     * @param int $count
     * @return \Illuminate\Support\Collection|InvoiceableModel[]
     */
    protected function createInvoiceables(int $count): Collection
    {
        return factory($this->invoiceableClass, $count)->create();
    }

    protected function createClientInvoice(InvoiceableModel $invoiceable, $paid = false)
    {
        /** @var ClientInvoice $invoice */
        $invoice = factory(ClientInvoice::class)->create();
        /** @var ClientInvoiceItem $item */
        $item = factory(ClientInvoiceItem::class)->make();
        $item->associateInvoiceable($invoiceable);
        $invoice->addItem($item);

        if ($paid) {
            $invoice->update([
                'amount' => 100,
                'amount_paid' => 100
            ]);
        } else {
            $invoice->update([
                'amount' => 100,
                'amount_paid' => 0
            ]);
        }


        return $invoice;
    }

    protected function createBusinessInvoice(InvoiceableModel $invoiceable, $paid = false)
    {
        /** @var BusinessInvoice $invoice */
        $invoice = factory(BusinessInvoice::class)->create();
        /** @var BusinessInvoiceItem $item */
        $item = factory(BusinessInvoiceItem::class)->make();
        $item->associateInvoiceable($invoiceable);
        $invoice->addItem($item);

        if ($paid) {
            $invoice->update([
                'amount' => 100,
                'amount_paid' => 100
            ]);
        }

        return $invoice;
    }

    protected function createCaregiverInvoice(InvoiceableModel $invoiceable, $paid = false)
    {
        /** @var CaregiverInvoice $invoice */
        $invoice = factory(CaregiverInvoice::class)->create();
        /** @var CaregiverInvoiceItem $item */
        $item = factory(CaregiverInvoiceItem::class)->make();
        $item->associateInvoiceable($invoiceable);
        $invoice->addItem($item);

        if ($paid) {
            $invoice->update([
                'amount' => 100,
                'amount_paid' => 100
            ]);
        }

        return $invoice;
    }
}
