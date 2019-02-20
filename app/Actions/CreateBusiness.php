<?php
namespace App\Actions;

use App\Business;

class CreateBusiness
{
    /**
     * @var \App\Actions\CreateBusinessChain
     */
    protected $createBusinessChain;


    public function create(array $data): ?Business
    {
        return Business::create($data);
    }

}