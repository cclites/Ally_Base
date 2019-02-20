<?php
namespace App\Actions;

use App\BusinessChain;
use App\Events\BusinessChainCreated;

class CreateBusinessChain
{

    public function create(array $data): ?BusinessChain
    {
        if ($chain = BusinessChain::create($data)) {
            event(new BusinessChainCreated($chain));
            return $chain;
        }

        return null;
    }
}