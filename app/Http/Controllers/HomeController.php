<?php

namespace App\Http\Controllers;

use App\Business;
use App\Caregiver;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Handle redirect from the root url.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function root()
    {
        if (\Auth::check()) {
            return redirect()->route('home');
        } else {
            return redirect('login');
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $method = camel_case(auth()->user()->role_type) . 'Dashboard';
        if (method_exists($this, $method)) {
            $role = auth()->user()->role;
            return $this->$method($role);
        }

        return view('home');
    }

    public function officeUserDashboard()
    {
        if (activeBusiness()->type === Business::TYPE_FRANCHISOR) {
            return view('business.dashboard.franchisor');
        }

        return redirect()->route('business.schedule.index');
    }

    public function clientDashboard()
    {
        return redirect()->route('client.invoices');
    }

    public function caregiverDashboard(Caregiver $caregiver)
    {
        if ($caregiver->isClockedIn()) {
            return redirect()->route('clocked_in');
        }
        return redirect()->route('schedule');
    }
}
