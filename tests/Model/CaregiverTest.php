<?php

namespace Tests\Model;

use App\Billing\Payments\Methods\BankAccount;
use App\Business;
use App\BusinessChain;
use App\Caregiver;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CaregiverTest extends TestCase
{
    use RefreshDatabase;

    public $caregiver;

    public function setUp()
    {
        parent::setUp();
        $this->caregiver = factory(Caregiver::class)->create();
    }

    public function testCaregiverCanBeCreated()
    {
        $this->assertTrue(true);
    }

    public function testCaregiverCanHaveADefaultBankAccount()
    {
        $account = factory(BankAccount::class)->make();
        $this->caregiver->setBankAccount($account);

        $definedAccount = $this->caregiver->bankAccount;
        $this->assertEquals($account->account_number, $definedAccount->account_number);
    }

    public function testCaregiverCanChangeDefaultBankAccount()
    {
        $account = factory(BankAccount::class)->make();
        $this->caregiver->setBankAccount($account);

        $account2 = factory(BankAccount::class)->make();
        $this->caregiver->bankAccounts()->save($account2);

        $this->caregiver->setBankAccount($account2);

        $this->assertEquals($this->caregiver->bankAccount->id, $account2->id);
    }

    public function testCaregiverCanWorkForMultipleChains()
    {
        $chain1 = factory(BusinessChain::class)->create();
        $chain2 = factory(BusinessChain::class)->create();

        $this->caregiver->businessChains()->attach($chain1);
        $this->caregiver->businessChains()->attach($chain2);

        $this->assertCount(2, $this->caregiver->businessChains);
    }

    public function testCaregiverBusinessesAttributeProvidesExplicitRelationship()
    {
        $chain1 = factory(BusinessChain::class)->create();
        $chain2 = factory(BusinessChain::class)->create();
        $this->caregiver->businessChains()->attach($chain1);
        $this->caregiver->businessChains()->attach($chain2);

        // Create 3 businesses across the 2 chains
        $business1 = $chain1->businesses()->save(factory(Business::class)->make());
        $business2 = $chain1->businesses()->save(factory(Business::class)->make());
        $business3 = $chain2->businesses()->save(factory(Business::class)->make());

        $this->assertCount(0, $this->caregiver->businesses);
        $this->assertCount(0, $this->caregiver->getBusinessIds());
        $this->assertFalse(in_array($business3->id, $this->caregiver->getBusinessIds()));

        $this->caregiver->ensureBusinessRelationship($business1);
        $this->caregiver->ensureBusinessRelationship($business3);
        $this->caregiver = $this->caregiver->fresh();
        
        $this->assertCount(2, $this->caregiver->fresh()->businesses);
        $this->assertCount(2, $this->caregiver->getBusinessIds());
        $this->assertTrue(in_array($business1->id, $this->caregiver->getBusinessIds()));
        $this->assertFalse(in_array($business2->id, $this->caregiver->getBusinessIds()));
        $this->assertTrue(in_array($business3->id, $this->caregiver->getBusinessIds()));
    }

    public function testCaregiverForBusinessesQuery()
    {
        $chain1 = factory(BusinessChain::class)->create();
        $chain2 = factory(BusinessChain::class)->create();
        $this->caregiver->businessChains()->attach($chain1);
        $this->caregiver->businessChains()->attach($chain2);

        // New caregiver only attached to chain2 (should not show up in results)
        $caregiver2 = factory(Caregiver::class)->create();
        $caregiver2->businessChains()->attach($chain2);

        // Create 3 businesses across the 2 chains
        $business1 = $chain1->businesses()->save(factory(Business::class)->make());
        $business2 = $chain1->businesses()->save(factory(Business::class)->make());
        $business3 = $chain2->businesses()->save(factory(Business::class)->make());

        $this->caregiver->ensureBusinessRelationships($chain1);
        $caregiver2->ensureBusinessRelationships($chain2);

        $result = Caregiver::forBusinesses([$business2->id])->first();
        $count = Caregiver::forBusinesses([$business2->id])->count();
        $this->assertEquals($this->caregiver->id, $result->id);
        $this->assertEquals(1, $count, 'Only one caregiver should show up as a result of the forBusinesses query.');
    }
}
