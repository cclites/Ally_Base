<?php
namespace App\Http\Controllers\Api\Telefony;

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
        if ($request->input('From')) {
            $this->number = $phoneNumber->input($request->input('From'));
        }
        $this->client = $telefony->findClientByNumber($this->number);
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