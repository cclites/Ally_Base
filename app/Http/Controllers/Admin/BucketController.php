<?php
namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Reports\AdminBucketReport;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BucketController extends Controller
{
    public function index(Request $request)
    {
        if ($request->expectsJson() && $request->input('start_date')) {
            $request->validate([
                'start_date' => 'required|date_format:m/d/Y',
                'end_date' => 'required|date_format:m/d/Y',
            ]);
            $report = new AdminBucketReport(new Carbon($request->start_date), new Carbon($request->end_date));
            return $report->rows();
        }

        return view('admin.reports.bucket');
    }
}