<?php

namespace App\Rules;

use App\Billing\Payer;
use App\Client;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class ValidEffectivePayer implements Rule
{
    /**
     * @var \App\Client
     */
    protected $client;
    /**
     * @var \Carbon\Carbon
     */
    protected $date;
    /**
     * @var \App\Billing\Payer
     */
    protected $payer;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Client $client, Carbon $date)
    {
        $this->client = $client;
        $this->date = $date->setTimezone($this->client->getTimezone());
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $payers = $this->client->getPayers($this->date->toDateString());
        $exists = $payers->where('payer_id', $value)->count() > 0;
        if (!$exists) {
            $this->payer = Payer::find($value);
        }

        return $exists;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if (!$this->payer) {
            return 'An assigned payer was not found.  We recommend you refresh and try again.';
        }
        $payerName = $this->payer->name();
        $localDate = $this->date->format('m/d/Y');
        return "The payer $payerName is not available on $localDate, please check the client's Payers tab.";
    }
}
