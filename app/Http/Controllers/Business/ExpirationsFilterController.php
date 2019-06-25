<?php

namespace App\Http\Controllers\Business;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Caregiver;
use App\Business;
use App\CaregiverLicense;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Reports\CertificationExpirationReport;

use Log;

class ExpirationsFilterController extends Controller
{
    protected $query;

    //{"caregiver_id":null,"days_range":"30","show_expired":"false","name":null,"business_id":"10"}

    public function index(Request $request){

        /*
        Log::info(json_encode($request->all()));

        if($request->caregiver_id){
            $caregiverIds[] = $request->caregiver_id;
        }else{

            $caregiverIds = Caregiver::forRequestedBusinesses([$request->business_id], null)->query();




        }

        $caregiverIds = Caregiver::forRequestedBusinesses([$request->business_id], null)->select();

        if(!$request->active){
            $caregiverIds->where('caregiver_active', $request->active);
        }

        if($request->caregiver_id){

        }else{
            $caregiverIds->pluck('id')->toArray();
        }

        Log::info($caregiverIds);
        */

        /*

        $report = new CertificationExpirationReport();
        $report->forBusinesses([$request->business_id])->orderBy('expires_at');
        $report->query()->whereIn('caregiver_id', $caregiverIds);


        if($request->show_expired == 'false'){
            $today = Carbon::today();
            $report->query()->where('expires_at', '<=', $today);
        }

        $expiresAt = Carbon::today()->addDays($request->days_range);
        $report->query()->where('expires_at', '<=', $expiresAt);

        if($request->name){
            $report->query()->where('name', $request->name);
        }

        $certifications = $report->rows();

        Log::info(json_encode($certifications));

        return response()->json($certifications);

        /*
        $this->query = CaregiverLicense::whereHas('caregiver', function($q){
            where('')
        });

        //return response()->json($request->all());

        $this->forCaregiverActive($request->active);
        $this->forExpirationType($request->name);
        $this->forLicensesExpiring($request->days_range);
        $this->forExpired($request->show_expired);

        return response()->json($this->query->get());
        */
    }

    public function forBusiness($businessId){
        Log::info("Business ID = $businessId");

        $this->query->where('business_id', $businessId);
    }

    public function forCaregiver($caregiverId){

        if($caregiverId){
            $this->query->where('id', $caregiverId);
        }

        Log::info("Caregiver = $caregiverId");
    }

    public function forCaregiverActive($active){
        Log::info("Active = $active");
    }

    public function forExpirationType($name){
        Log::info("Name = $name");
    }

    public function forLicensesExpiring($range){
        Log::info("Range = $range");
    }

    public function forExpired($expired){
        Log::info("Expired = $expired");
    }
}

//"caregiver_id":null,"days_range":"30","show_expired":"false","active":null,"name":null,"business_id":"57"}