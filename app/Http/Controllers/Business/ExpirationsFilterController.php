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

    public function index(Request $request){

        $query = Caregiver::select();

        if($request->caregiver_id){
            $query = $query->where('id', $request->caregiver_id);
        }

        $query = $query->forRequestedBusinesses([$request->business_id], null);

        if($request->active){

            $query = $query->whereHas('user', function($q) use($request){
                return $q->where('active', $request->active);
            });
        }

        $caregiverIds = $query->pluck('id')->toArray();

        $report = new CertificationExpirationReport();
        $report->forBusinesses([$request->business_id])->orderBy('expires_at');
        $report->query()->whereIn('caregiver_id', $caregiverIds);

        $expiresAt = Carbon::today()->addDays($request->days_range);
        $report->query()->where('expires_at', '<=', $expiresAt);

        if($request->name){
           $report->query()->where('name', $request->name);
        }

        $certifications = $report->rows();

        return response()->json($certifications);

    }
}
