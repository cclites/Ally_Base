<?php


namespace App\Admin\Queries;

use App\Caregiver1099;
use App\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


class Caregiver1099Query extends BaseQuery
{
    protected $query;

    protected $start = '2017-01-01 00:00:00';
    protected $end = '2017-12-31 23:59:59';

    function getModelInstance(): Model
    {
        return new Caregiver1099();
    }

    public function caregiver1099s(){

        //two queries. One to get Caregiver_1099s.
        //$this->query = Caregiver1099::where('business_id', 9);

        //return $this->query;

    }

    public function forBusiness($businessId){
        $this->query->where('business_id', $businessId);
        return $this;
    }

    public function forClient($clientId){
        $this->where('business_id', $clientId);
        return $this;
    }

    public function forCaregiver($caregiverId){
        $this->where('business_id', $caregiverId);
        return $this;
    }

    public function forCaregiver1099($caregiver1099Id){
        $this->where('id', $caregiver1099Id);
        return $this;
    }

    public function forCaregiver1099Type($type){

    }

    public function isTrasmitted($transmitted_at){
        $this->whereIsNotNull('transmittedBy');
        return $this;
    }

    public function caregiver1099_v2(){

        $this->query = Client::query();

        $this->query->with([
            'shifts',
            'shifts.payments',
            'shifts.shift_cost_history',
            'user',
            'addresses',
            'caregivers',
            'caregivers.addresses',
            'caregivers.user',
            'caregivers.businesses'
        ])
            ->where('business_id', 9);

        return $this->query
                ->first()
                ->map(function(Client $client){
                    return [
                        'client_name' => $client->user->nameLastFirst()
                    ];
                });

        /*
        return collect( Client::with([
                    'shifts',
                    'shifts.payments',
                    'shifts.shift_cost_history',
                    'user',
                    'addresses',
                    'caregivers',
                    'caregivers.addresses',
                    'caregivers.user',
                    'caregivers.businesses'
                ])
                ->where('business_id', 9)
                ->first()
        )->map(function (Client $row){

                    return [
                        'client_name' => $row->firstLastName(),
                        'shifts' => $row->shifts->payments->amount(),
                    ];

                });
        */
    }


}

/*
 * ->join('contacts', 'users.id', '=', 'contacts.user_id')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->select('users.*', 'contacts.phone', 'orders.price')
 *
 *
    INNER JOIN shifts s ON s.client_id = c.id
    INNER JOIN payments p ON s.payment_id = p.id
    INNER JOIN shift_cost_history h ON h.id = s.id
    INNER JOIN users u1 ON u1.id = s.client_id
    INNER JOIN users u2 ON u2.id = s.caregiver_id
    INNER JOIN caregivers c2 ON c2.id = u2.id
    INNER JOIN businesses b ON c.business_id = b.id
 */