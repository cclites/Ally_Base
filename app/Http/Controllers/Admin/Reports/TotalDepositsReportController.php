<?php


namespace App\Http\Controllers\Admin\Reports;


use App\BusinessChain;
use App\Reports\TotalDepositsReport;
use Http\Message\Authentication\Chain;
use Illuminate\Http\Request;

class TotalDepositsReportController
{
    public function index(Request $request, TotalDepositsReport $report){

        if ( $request->filled( 'json' ) ) {

            $timezone = auth()->user()->role->getTimezone();

            $report->setTimezone( $timezone )
                ->applyFilters(
                    $request->startdate,
                    $request->enddate);

            $data = $report->rows();

            $totals = [
                'amount' => $data->sum( 'amount' )
            ];

            $data = $this->createSummary( $report->rows() );

            return response()->json([ 'data' => $data, 'totals' => $totals ]);
        }

        return view_component(
            'total-deposits-report',
            'Total Deposits Report',
            [],
            [
                'Home' => route('home'),
                'Reports' => route('business.reports.index')
            ]
        );
    }

    public function createSummary($data): iterable
    {
        $set = [];

        //This was done using for-each because there is no chain_id for deposits created
        //before this report was updated.
        foreach($data as $item){

            $key = $item['name'];

            if(!isset($set[$key])){
                $set[$key] = [
                    'name'=>$item['name'],
                    'amount' => $item['amount'],
                ];
            }else{
                $set[$key]['amount'] += $item['amount'];
            }
        }

        return array_values($set);
    }
}