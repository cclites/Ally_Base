<?php
namespace App\Http\Controllers\Api\Telefony;

use App\Exceptions\TelefonyMessageException;
use App\Http\Controllers\Controller;
use App\PhoneNumber;
use App\Services\TelefonyManager;
use Illuminate\Http\Request;
use Twilio\Security\RequestValidator;

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
            if (\App::runningInConsole()) {
                return;
            }
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
    protected function mainMenuResponse()
    {
        $this->telefony->say('Returning to the main menu');
        $this->telefony->redirect(route('telefony.greeting'), ['method' => 'GET']);
        return $this->telefony->response();
    }

    protected function authorizeRequest(Request $request)
    {
        $twilioSid = config('services.twilio.sid');
        $twilioToken = config('services.twilio.token');

        $requestValidator = new RequestValidator($twilioToken);
        if (app()->environment() === 'production' && ! $requestValidator->validate(
                $request->header('X-Twilio-Signature'),
                $request->fullUrl(),
                $request->toArray()
            )) {
            return false;
        }

        if (\Validator::make($request->all(), [
            'MessageSid' => 'required|string|max:34|min:34',
            'AccountSid' => "required|string|max:34|min:34|in:$twilioSid",
            // 'MessagingServiceSid' => 'required|string|max:34|min:34',
            'To' => 'required|string',
            'From' => 'required|string',
            'Body' => 'required|string',
        ])->fails()) {
            return false;
        }

        return true;
    }

    protected function unauthorizedResponse()
    {
        return $this->xmlResponse('<error>Unauthenticated</error>', 401);
    }


    protected function xmlResponse(string $xml, int $status = 200)
    {
        if (mb_substr_count($xml, '<') < 2 && !mb_substr_count($xml, 'root')) {
            $xml = "<root>$xml</root>";
        }
        return response($xml, $status)->header('Content-Type', 'text/xml; charset=UTF-8');
    }
}
