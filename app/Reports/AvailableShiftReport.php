<?php


namespace App\Reports;


use App\Schedule;
use Carbon\Carbon;

class AvailableShiftReport extends BaseReport
{
    protected $query;

    protected $start;

    protected $end;



    public function __construct(){
        $this->query = Schedule::with(['client', 'service', 'services.service']);
    }

    /**
     * @inheritDoc
     */
    public function query()
    {
        return $this->query;
    }

    public function applyFilters(int $businesses, string $start, string $end, ?int $client, ?string $city, ?int $service): self
    {
        $this->query->where('business_id', $businesses);

        $this->start = (new Carbon($start . ' 00:00:00'));
        $this->end = (new Carbon($end . ' 23:59:59'));

        $this->query->whereBetween('starts_at', [$this->start, $this->end]);

        if(filled($client)){
            $this->query->where('client_id', $client);
        }

        if(filled($city)){
            $this->query->whereHas('client', function($q) use($city){
                $q->whereHas('addresses', function($q1) use($city){
                    $q1->where('city', $city);
                });
            });
        }

        if(filled($service)){
            $this->query->whereHas('service', function($q) use($service){
                $q->where('id', $service);
            });
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function results()
    {
        return $this->query
            ->get()
            ->groupBy('client_id')
            ->values()
            ->map(function($schedule){

                $data = $schedule->map(function($item){

                    if($item->service){
                        $services[] = $item->service->name;
                    }elseif($item->services){
                        $services = $item->services->map(function($service){
                            return $service->service->name;
                        });
                    }

                    return collect($services)->map(function($service) use($item){

                        return [
                            'service_name' => $service,
                            'day'=>$item->starts_at->format('l'),
                            'date' => $item->starts_at->format('m/d/Y'),
                            'start_time' => $item->starts_at->format('g:i A'),
                            'end_time' => $item->starts_at->addMinutes($item->duration)->format('g:i A'),
                        ];
                    });
                });

                return[
                    'client_name' => $schedule->first()->client->nameLastFirst,
                    'client_city' => $schedule->first()->client->addresses->first()->city ?? '',
                    'client_services' => $data,
                    'case_manager' => $schedule->first()->client->case_manager,
                ];

            });
    }
}