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


        Log::info(json_encode($request->all()));

        $caregiverIds = Caregiver::forRequestedBusinesses([$request->business_id], null)
                        ->pluck('id')->toArray();

        $report = new CertificationExpirationReport();
        $report->forBusinesses([$request->business_id])->orderBy('expires_at');
        $report->query()->whereIn('caregiver_id', $caregiverIds);

        /*
        if($request->show_expired == 'false'){
            $today = Carbon::today();
            $report->query()->where('expires_at', '>=', $today);
        }*/

        $expiresAt = Carbon::today()->addDays($request->days_range);
        $report->query()->where('expires_at', '>=', $expiresAt);

        if($request->name){
            $report->query()->where('name', $request->name);
        }

        $certifications = $report->rows();

        if($request->active){
            $certifications = $certifications->where('caregiver_active', $request->active)->get();
        }

        Log::info($certifications);

        return response()->json($certifications);

    }
}
