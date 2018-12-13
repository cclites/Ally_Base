<?php


namespace App\Traits;


use App\Address;
use App\PhoneNumber;

trait HasAddressesAndNumbers
{
    ////////////////////////////////////
    //// Relationships
    ////////////////////////////////////

    public function addresses()
    {
        return $this->hasMany(Address::class, 'user_id', 'id');
    }

    public function phoneNumbers()
    {
        return $this->hasMany(PhoneNumber::class, 'user_id', 'id');
    }

    /**
     * Get the phone number where text messages should be sent.
     *
     * @return \App\PhoneNumber
     */
    public function smsNumber()
    {
        return $this->hasOne(PhoneNumber::class, 'user_id', 'id')
                    ->where('receives_sms', true);
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    /**
     * Create, or update, an address of $type
     *
     * @param string $type
     * @param array $data
     * @return Address|false
     */
    public function saveAddress($type, array $data)
    {
        $address = $this->addresses()->where('type', $type)->first() ?? new Address(['user_id' => $this->id, 'type' => $type]);
        $address->fill($data);
        return $this->addresses()->save($address);
    }

    /**
     * Delete an address
     *
     * @param int $id
     * @return bool
     */
    public function deleteAddress($id)
    {
        return $this->addresses()->where('id', $id)->limit(1)->delete();
    }

    /**
     * Create a new phone number of $type
     *
     * @param $type
     * @param $number
     * @param $extension
     * @return PhoneNumber|false
     */
    public function addPhoneNumber($type, $number, $extension = null)
    {
        $phone = new PhoneNumber(['user_id' => $this->id, 'type' => $type]);
        $phone->input($number, $extension);
        return $this->phoneNumbers()->save($phone);
    }

    /**
     * Delete a phone number
     *
     * @param int $id
     * @return bool
     */
    public function deletePhoneNumber($id)
    {
        return $this->phoneNumbers()->where('id', $id)->limit(1)->delete();
    }
}