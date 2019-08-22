<?php


namespace App\Http\Controllers\Admin\Reports;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Client;
use App\Caregiver;
use App\Rules\ValidSSN;
use Crypt;

class AdminBadSsnReportController extends Controller
{

    public function index(Request $request)
    {
        $label = '';

        $report = collect();
        if($request->input('type') === 'client'){
            $label = "Client";
            $clients = Client::with('user')->get();
            foreach($clients as $client){
                try{
                    $ssn = $client->ssn;
                    if(!$this->validSSN($ssn)){
                        $report->push(['name'=> $client->nameLastFirst(), 'business'=>$client->business->name, 'type'=>'client']);
                    }
                }catch(\Exception $e){
                    //swallow
                }
            }
        }elseif($request->input('type') === 'caregiver')
        {
            $label = "Caregiver";
            $caregivers = Caregiver::with('user')->get();
            foreach($caregivers as $caregiver){
                try{
                    $ssn = $caregiver->ssn;
                    if(!$this->validSSN($ssn)){
                        $report->push(['name'=> $caregiver->nameLastFirst(), 'business'=>$caregiver->business->name, 'type'=>'caregiver']);
                    }
                }catch(\Exception $e){
                    //swallow
                }
            }
        }

        $report = $report->values();

        return view_component(
            'bad-ssn-report',
            'Bad ' . $label . 'SSNs Report',
            compact(['report']),
            [
                'Home' => route('home'),
                'Reports' => route('business.reports.index')
            ]
        );

    }

    public function validSSN($ssn){

        if(!filled($ssn)){
            return true;
        }

        $pattern = '/(\d{3}|\*{3})-(\d{2}|\*{2})-(\d{4}|\*{4})/';
        return preg_match($pattern, $ssn);
    }

}