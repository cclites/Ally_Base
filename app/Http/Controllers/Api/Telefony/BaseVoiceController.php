<?php
namespace App\Http\Controllers\Api\Telefony;

use App\Exceptions\TelefonyMessageException;
use App\Services\TelefonyManager;
use Illuminate\Http\Request;
use App\PhoneNumber;

abstract class BaseVoiceController extends BaseTelefonyController
{
    /**
     * @var \App\PhoneNumber
     */
    protected $number;

    /**
     * @var \App\Client|null
     */
    protected $client;

    /**
     * BaseVoiceController constructor.
     *
     * @param Request $request
     * @param TelefonyManager $telefony
     * @param PhoneNumber $phoneNumber
     * @throws TelefonyMessageException
     */
    public function __construct(Request $request, TelefonyManager $telefony, PhoneNumber $phoneNumber)
    {
        parent::__construct($request, $telefony, $phoneNumber);

        if (!$request->input('From')) {
            if (\App::runningInConsole()) {
                return;
            }
            abort(403);
        }

        $this->number = $phoneNumber->input($request->input('From'));
        $this->client = $telefony->findClientByNumber($this->number);
        if (!$this->client) {
            throw new TelefonyMessageException('The number you are calling from is not recognized.  The phone number needs to be linked to the client account for verification purposes.');
        }
    }
}