<?php
namespace App\Http\Requests;

use App\Client;
use App\User;

/**
 * Class BusinessClientRequest
 * Description:  This class extends the standard business request to use the business_id from the client.
 * NOTE: When using this class, the validation rules should require that the client field (client_id) exists.
 *
 * @package App\Http\Requests
 */
abstract class BusinessClientRequest extends BusinessRequest
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $clientField = 'client_id';

    public function getClient()
    {
        if (!$this->client) {
            request()->validate([$this->clientField => 'required|exists:clients,id']);
            $this->client = Client::findOrFail($this->input($this->clientField));
        }

        return $this->client;
    }

    public function getBusinessId(User $user = null)
    {
        return $this->getClient()->business_id;
    }

    protected function getBusinessRulesForUser(User $user = null)
    {
        return ['required', 'exists:businesses,id'];
    }
}