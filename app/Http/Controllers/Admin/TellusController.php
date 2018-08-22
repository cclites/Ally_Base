<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TellusXMLService;
use App\Shift;
use Illuminate\Http\Request;

class TellusController extends Controller
{
    public function index()
    {
        return view('admin.tellus.index');
    }

    public function download(TellusXMLService $service, Shift $shift)
    {
        $service->addShift($shift);
        return $service->downloadXml();
    }
}