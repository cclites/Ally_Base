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
            $this->query->whereHas('clients', function($q) use($city){
                $q->whereHas('addresses', function($q1) use($city){
                    $q1->where('city', $city);
                });
            });
        }

        if(filled($service)){
            $this->query->whereHas('services', function($q) use($service){
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
            ->map(function(Schedule $schedule){

                if($schedule->service){
                    $services[] = $schedule->service->name;
                }elseif($schedule->services){
                    $services = $schedule->services->map(function($service){
                        return $service->service->name;
                    });
                }

                return[
                    'client_name' => $schedule->client->nameLastFirst,
                    'client_city' => $schedule->client->addresses->first()->city,
                    'client_services' => $services,
                    'case_manager' => $schedule->client->case_manager,
                    'day' => $schedule->starts_at->format('l'),
                    'date' => $schedule->starts_at->format('m/d/Y'),
                    'start_time' => $schedule->starts_at->format('g:i A'),
                    'end_time' => $schedule->starts_at->addMinutes($schedule->duration)->format('g:i A'),
                ];
            });
    }
}