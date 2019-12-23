<?php

namespace Tests\Model;

use App\Billing\Payments\Methods\BankAccount;
use App\Business;
use App\BusinessChain;
use App\Caregiver;

use App\Billing\CaregiverInvoice;
use Tests\CreatesBusinesses;


use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;


class CaregiverTest extends TestCase
{
    use RefreshDatabase, CreatesBusinesses;

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

        $chain1->assignCaregiver($this->caregiver);
        $chain2->assignCaregiver($this->caregiver);

        $this->assertCount(2, $this->caregiver->businessChains);
    }

    public function testCaregiverBusinessesAttributeProvidesExplicitRelationship()
    {
        $chain1 = factory(BusinessChain::class)->create();
        $chain2 = factory(BusinessChain::class)->create();

        // Create 3 businesses across the 2 chains
        $business1 = $chain1->businesses()->save(factory(Business::class)->make());
        $business2 = $chain1->businesses()->save(factory(Business::class)->make());
        $business3 = $chain2->businesses()->save(factory(Business::class)->make());

        $this->assertCount(0, $this->caregiver->businesses);
        $this->assertCount(0, $this->caregiver->getBusinessIds());
        $this->assertFalse(in_array($business3->id, $this->caregiver->getBusinessIds()));

        $business1->assignCaregiver($this->caregiver);
        $business3->assignCaregiver($this->caregiver);
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

        // Create 3 businesses across the 2 chains
        $business1 = $chain1->businesses()->save(factory(Business::class)->make());
        $business2 = $chain1->businesses()->save(factory(Business::class)->make());
        $business3 = $chain2->businesses()->save(factory(Business::class)->make());

        $chain1->assignCaregiver($this->caregiver);
        
        // New caregiver only attached to chain2 (should not show up in results)
        $caregiver2 = factory(Caregiver::class)->create();
        $chain2->assignCaregiver($caregiver2);

        $result = Caregiver::forBusinesses([$business2->id])->first();
        $count = Caregiver::forBusinesses([$business2->id])->count();
        $this->assertEquals($this->caregiver->id, $result->id);
        $this->assertEquals(1, $count, 'Only one caregiver should show up as a result of the forBusinesses query.');
    }

    public function testCaregiverCanBeDeactivated(){

        $this->createBusinessWithUsers();
        $this->actingAs( $this->officeUser->user );

        $this->deleteJson(route('business.caregivers.destroy', ['caregiver' => $this->caregiver]));
        $this->assertEquals(0, $this->caregiver->fresh()->active, "Caregiver was not deactivated");
    }

    public function testCaregiverHasOpenInvoicesCanNotDeactivate(){

        $this->createBusinessWithUsers();
        $this->actingAs( $this->officeUser->user );

        factory(CaregiverInvoice::class)->create(['caregiver_id'=>$this->caregiver->id, 'amount'=>100, 'amount_paid'=>0]);

        $this->deleteJson(route('business.caregivers.destroy', ['caregiver' => $this->caregiver]))
            ->assertStatus(400, 'Should not be able to deactivate this caregiver');
    }

}
