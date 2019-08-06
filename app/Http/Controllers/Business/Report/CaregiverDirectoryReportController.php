<?php


namespace App\Http\Controllers\Business\Report;


use App\Caregiver;
use App\CustomField;
use App\Http\Controllers\Business\BaseController;
use App\Reports\CaregiverDirectoryReport;
use App\Responses\ErrorResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CaregiverDirectoryReportController extends BaseController
{
    /**
     * Shows the page to generate the caregiver directory
     *
     * @return Response
     */
    public function index()
    {
        $caregivers = Caregiver::forRequestedBusinesses()
            ->with(['address', 'user', 'user.emergencyContacts', 'user.phoneNumbers'])
            ->with('meta')
            ->get()->map(function($caregiver){

                $caregiver->phone = $caregiver->user->notification_phone;
                $caregiver->emergency_contact = $caregiver->user->emergency_contact ? $caregiver->user->formatEmergencyContact() : '-';
                $caregiver->referral = $caregiver->referralSource ? $caregiver->referralSource->name : '-';
                $caregiver->certification = $caregiver->certification ? $caregiver->certification : '-';
                $caregiver->smoking_okay = $caregiver->smoking_okay ? "Yes" : "No";
                $caregiver->ethnicity = $caregiver->ethnicity ? $caregiver->ethnicity : '-';
                $caregiver->medicaid_id = $caregiver->medicaid_id ? $caregiver->medicaid_id : '-';
                $caregiver->gender = $caregiver->user->gender ? $caregiver->user->gender : '-';

                return $caregiver;

            });

        $fields = CustomField::forAuthorizedChain()
            ->where('user_type', 'caregiver')
            ->with('options')
            ->get();

        return view('business.reports.caregiver_directory', compact('caregivers', 'fields'));
    }

    /**
     * Handle the request to generate the caregiver directory
     *
     * @param \Illuminate\Http\Request $request
     * @return Response
     */
    public function generateCaregiverDirectoryReport(Request $request)
    {
        $report = new CaregiverDirectoryReport();
        $report->forRequestedBusinesses();
        $report->query()->join('users','caregivers.id','=','users.id');

        if($request->has('filter_active')) {
            $report->where('users.active', $request->filter_active);
        }

        $report->applyColumnFilters($request->except(['filter_start_date','filter_end_date','filter_active']));

        if ($request->has('export') && $request->export == true) {
            return $report->download();
        }

        return $report->rows();
    }
}