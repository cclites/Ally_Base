<?php

namespace App;

use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToOneBusiness;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CommunicationLog extends AuditableModel
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    public $with = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    /**
     * Gets logs that are sent in between given start and end dates.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param null|string $start
     * @param null|string $end
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWhereSentBetween($query, $start, $end)
    {
        if (empty($start) || empty($end)) {
            return $query;
        }

        return $query->whereBetween('sent_at', [
            (new Carbon($start . ' 00:00:00', 'America/New_York'))->setTimezone('UTC'),
            (new Carbon($end . ' 23:59:59', 'America/New_York'))->setTimezone('UTC')
        ]);
    }

    /**
     * Get the logs with the given channel type.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param null|string $channel
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForChannel($query, $channel)
    {
        if (empty($channel)) {
            return $query;
        }

        return $query->where('channel', $channel);
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************
}
