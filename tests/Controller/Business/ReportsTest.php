<?php

namespace Tests\Controller\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Business;
use App\OfficeUser;
use App\Caregiver;
use App\Client;

class ReportsTest extends TestCase {
    use RefreshDatabase, WithFaker;

    protected $officeUser;
    protected $business;

    public function setUp() : void
    {
        parent::setUp();

        $this->business = factory(Business::class)->create();
        $this->officeUser = factory(OfficeUser::class)->create();

        $this->actingAs($this->officeUser->user);
        $this->officeUser->businesses()->attach($this->business->id);
    }

    /**
     * @test
     *
     * verifying the results of the Caregiver Directory Report
     */
    public function caregiver_directory_report_works_properly() {
        $this->withoutExceptionHandling();

        $data = factory(Caregiver::class, 15)->create()->each(function ($caregiver) {
            $caregiver->businesses()->attach($this->business);
        });

        $query_string = '?json=1';
        // $query_string .= '&start_date=08/02/2019';
        // $query_string .= '&end_date=08/10/2019';
        $query_string .= '&current_page=1';
//        $query_string .= '&per_page=5';
        $query_string .= '&active=true';

        $results = $this->get(route('business.reports.caregiver_directory') . $query_string)
                        ->assertSuccessful()
                        ->assertJsonCount(15, 'rows');
    }

    /**
     * @test
     *
     * verifying the results of the Caregiver Directory Report
     */
    public function client_directory_report_works_properly() {
        $this->withoutExceptionHandling();

        $data = factory(Client::class, 15)->create([

            'business_id' => $this->business->id
        ]);

        $query_string = '?json=1';
        // $query_string .= '&start_date=08/02/2019';
        // $query_string .= '&end_date=08/10/2019';
        $query_string .= '&current_page=1';
//        $query_string .= '&per_page=5';
        $query_string .= '&active=true';

        $results = $this->get(route('business.reports.client_directory') . $query_string)
                        ->assertSuccessful()
                        ->assertJsonCount(15, 'rows');
    }

    /** @test */
    public function client_birthday_report_returns_all_clients() {
        $query_string = '?type=clients';
        $clients = factory(Client::class, 2)->create([
            'business_id' => $this->business->id
        ]);

        $this->get('business/reports/data/birthdays' . $query_string)
             ->assertSuccessful()
             ->assertJsonCount(2)
             ->assertJsonFragment(["id" => $clients[0]->id, "email" => $clients[0]->email]);
    }

    /** @test */
    public function client_birthday_report_filters_by_client_id() {
        $clients = factory(Client::class, 2)->create([
            'business_id' => $this->business->id
        ]);
        $query_string = '?type=clients&id=' . $clients[0]->id;

        $this->get('business/reports/data/birthdays' . $query_string)
             ->assertSuccessful()
             ->assertJsonCount(1)
             ->assertJsonFragment(["id" => $clients[0]->id, "email" => $clients[0]->email]);
    }

    /** @test */
    public function client_birthday_report_filters_by_client_type() {
        $client1 = factory(Client::class)->create([
            'business_id' => $this->business->id,
            'client_type' => 'private_pay'
        ]);

        factory(Client::class)->create([
            'business_id' => $this->business->id,
            'client_type' => 'medicaid'
        ]);

        $query_string = '?type=clients&clientType=' . $client1->client_type;

        $this->get('business/reports/data/birthdays' . $query_string)
             ->assertSuccessful()
             ->assertJsonCount(1)
             ->assertJsonFragment(["id" => $client1->id, "email" => $client1->email]);
    }

    /** @test */
    public function client_birthday_report_returns_client_type_if_id_is_null() {
        $client1 = factory(Client::class)->create([
            'business_id' => $this->business->id,
            'client_type' => 'private_pay'
        ]);

        factory(Client::class)->create([
            'business_id' => $this->business->id,
            'client_type' => 'medicaid'
        ]);

        $query_string = '?type=clients&clientType=' . $client1->client_type . '&id=All';

        $this->get('business/reports/data/birthdays' . $query_string)
             ->assertSuccessful()
             ->assertJsonCount(1)
             ->assertJsonFragment(["id" => $client1->id, "email" => $client1->email]);
    }

    /** @test */
    public function client_birthday_report_filters_by_date_range() {
        $start_date = '07/28/2020';
        $end_date = '01/28/2021';
        $clients = factory(Client::class, 3)->create([
            'business_id' => $this->business->id,
        ]);

        $user = $clients[0]->user;
        $user->date_of_birth = '1985/07/04';
        $user->save();

        $user2 = $clients[1]->user;
        $user2->date_of_birth = '1990/08/04';
        $user2->save();

        $user3 = $clients[2]->user;
        $user3->date_of_birth = '2000/01/04';
        $user3->save();

        $query_string = '?type=clients&start_date=' . $start_date . '&end_date=' . $end_date;

        $this->get('business/reports/data/birthdays' . $query_string)
             ->assertSuccessful()
             ->assertJsonCount(2)
             ->assertJsonFragment(["id" => $clients[1]->id, "email" => $clients[1]->email])
             ->assertJsonFragment(["id" => $clients[2]->id, "email" => $clients[2]->email]);
    }

    /** @test */
    public function caregiver_birthday_report_returns_all_caregivers() {
        $query_string = '?type=caregivers';
        $caregivers = factory(Caregiver::class, 2)->create()->each(function ($caregiver) {
            $caregiver->businesses()->attach($this->business);
        });

        $this->get('business/reports/data/birthdays' . $query_string)
             ->assertSuccessful()
             ->assertJsonCount(2)
             ->assertJsonFragment(["id" => $caregivers[0]->id, "email" => $caregivers[0]->email]);
    }

    /** @test */
    public function caregiver_birthday_report_filters_by_caregiver_id() {
        $caregivers = factory(Caregiver::class, 2)->create()->each(function ($caregiver) {
            $caregiver->businesses()->attach($this->business);
        });

        $query_string = '?type=caregiver&id=' . $caregivers[0]->id;

        $this->get('business/reports/data/birthdays' . $query_string)
             ->assertSuccessful()
             ->assertJsonCount(1)
             ->assertJsonFragment(["id" => $caregivers[0]->id, "email" => $caregivers[0]->email]);
    }

    /** @test */
    public function caregiver_birthday_report_filters_by_date_range() {
        $start_date = '07/28/2020';
        $end_date = '01/28/2021';
        $caregivers = factory(Caregiver::class, 3)->create()->each(function ($caregiver) {
            $caregiver->businesses()->attach($this->business);
        });

        $user = $caregivers[0]->user;
        $user->date_of_birth = '1985/07/04';
        $user->save();

        $user2 = $caregivers[1]->user;
        $user2->date_of_birth = '1990/08/04';
        $user2->save();

        $user3 = $caregivers[2]->user;
        $user3->date_of_birth = '2000/01/04';
        $user3->save();

        $query_string = '?type=caregivers&start_date=' . $start_date . '&end_date=' . $end_date;

        $this->get('business/reports/data/birthdays' . $query_string)
             ->assertSuccessful()
             ->assertJsonCount(2)
             ->assertJsonFragment(["id" => $caregivers[1]->id, "email" => $caregivers[1]->email])
             ->assertJsonFragment(["id" => $caregivers[2]->id, "email" => $caregivers[2]->email]);
    }
}
