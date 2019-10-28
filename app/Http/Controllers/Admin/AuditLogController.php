<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Audit;

class AuditLogController extends Controller
{
    /**
     * Displays a lit of audits and the audit report page.
     *
     * @return void
     */
    public function index()
    {
        if (request()->expectsJson()) {
            return response()->json([]);
            $dates = [
                Carbon::parse(request()->start)->startOfDay(),
                Carbon::parse(request()->end)->endOfDay()
            ];

            return Audit::whereBetween('created_at', $dates)
                ->with('user')
                ->get();
        }

        return view('admin/reports/audit');
    }
}
