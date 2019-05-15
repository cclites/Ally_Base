<?php
namespace App\Actions;

use App\Billing\Contracts\ChargeableInterface;
use App\Client;
use App\Events\ClientCreated;
use App\OnboardStatusHistory;
use App\ClientAgreementStatusHistory;
use App\Caregiver;
use App\BusinessChain;

class CreateCaregiver
{
    const AUTO_EMAIL = "tmp";

    public function create(array $data, BusinessChain $chain): ?Caregiver
    {
        if (empty($data['email'])) {
            $data['email'] = self::AUTO_EMAIL;
        }

        if (empty($data['username'])) {
            $data['username'] = Caregiver::getAutoUsername();
        }

        if ($caregiver = Caregiver::create($data)) {
            if ($caregiver->email === self::AUTO_EMAIL) {
                $caregiver->setAutoEmail()->save();
            }

            if ($chain->caregivers()->save($caregiver)) {
                $caregiver->ensureBusinessRelationships($chain);
                $caregiver->setAvailability([]); // sets default availability
                return $caregiver;
            }
    
            return null;
        }

        return null;
    }
}