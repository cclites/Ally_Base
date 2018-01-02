<?php
namespace App\Http\Controllers\Api\Telefony;

use App\Exceptions\TelefonyMessageException;
use App\Http\Controllers\Controller;
use App\PhoneNumber;
use App\Services\TelefonyManager;
use Illuminate\Http\Request;

abstract class BaseTelefonyController extends Controller
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
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var TelefonyManager
     */
    protected $telefony;

    public function __construct(Request $request, TelefonyManager $telefony, PhoneNumber $phoneNumber)
    {
//        $this->middleware('twilio');
        $this->request = $request;
        $this->telefony = $telefony;

        if (!$request->input('From')) {
            abort(403);
        }

//        if ($request->getContentType() !== 'xml') {
//            abort(403);
//        }

        $this->number = $phoneNumber->input($request->input('From'));
        $this->client = $telefony->findClientByNumber($this->number);
        if (!$this->client) {
            throw new TelefonyMessageException('The number you are calling from is not recognized.  The phone number needs to be linked to the client account for verification purposes.');
        }
    }

    /**
     * Return main menu response.
     */
    public function mainMenuResponse()
    {
        $this->telefony->say('Returning to the main menu');
        $this->telefony->redirect(route('telefony.greeting'), ['method' => 'GET']);
        return $this->telefony->response();
    }

}