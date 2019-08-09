<?php
namespace App\Reports;

use App\Client;
use App\Traits\IsDirectoryReport;
use App\CustomField;

class ClientDirectoryReport extends BusinessResourceReport
{
    use IsDirectoryReport;

    private $per_page = 10; // simple default for 'limit'
    private $current_page = 1; // simple default.. maybe it should start at zero?
    private $total_count;

    private $active_filter;
    private $client_type;

    /**
     * @var bool
     */
    protected $generated = false;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $rows;

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * ClientDirectoryReport constructor.
     */
    public function __construct()
    {
        $this->query = Client::with([ 'user', 'address' ]);
    }

    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return $this->query;
    }

    /**
     * Filter by active status.
     *
     * @param $status
     * @return CaregiverAccountSetupReport
     */
    public function setClientTypeFilter( $type ) : self
    {
        $this->client_type = $type;

        return $this;
    }

    /**
     * Filter by active status.
     *
     * @param $status
     * @return CaregiverAccountSetupReport
     */
    public function setActiveFilter( $active ) : self
    {
        $this->active_filter = $active;

        return $this;
    }

    /**
     * Set number of records to pagniate per page
     *
     * @param $status
     * @return CaregiverAccountSetupReport
     */
    public function setPageCount( $count ) : self
    {
        $this->per_page = $count;

        return $this;
    }

    /**
     * Set number of records to pagniate per page
     *
     * @param $status
     * @return CaregiverAccountSetupReport
     */
    public function setCurrentPage( $page ) : self
    {
        $this->current_page = $page;

        return $this;
    }
   
    /**
     * 
     * public accessor for the total count
     */
    public function getTotalCount()
    {
        return $this->total_count;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results()
    {

        switch( $this->active_filter ){

            case 'true':

                $this->query()->active();
                break;
            case 'false':

                $this->query()->inactive();
                break;
            default:

                break;
        }

        if( $this->client_type ) $this->query()->where( 'client_type', $this->client_type );

        $this->total_count = $this->query()->with( 'meta' )
            ->count();


        $this->query()->limit( $this->per_page )->offset( $this->per_page * ( $this->current_page - 1 ) );


        $clients = $this->query()->get();


        $this->generated = true;
        $customFields = CustomField::forAuthorizedChain()->where( 'user_type', 'client' )->get();

        $rows = $clients->map( function( Client $client ) use( &$customFields ) {

            $result = [

                'id'          => $client->id,
                'first_name'  => $client->user->firstname,
                'last_name'   => $client->user->lastname,
                'email'       => $client->user->email,
                'active'      => $client->active ? 'Active' : 'Inactive',
                'address'     => $client->address ? $client->address->full_address : '',
                'client_type' => $client->client_type,
                'date_added'  => $client->user->created_at->format( 'm-d-Y' ),
                'created_at'  => $client->user->created_at->format( 'm-d-Y' ),
            ];

            // Add the custom fields to the report row
            foreach( $customFields as $field ) {

                if( $meta = $client->meta->where( 'key', $field->key )->first() ) {

                    $result[ $field->key ] = $meta->display();
                    continue;
                }

                $result[ $field->key ] = $field->default;
            }

            return $result;
        });

        $rows = $this->filterColumns( $rows );
        return $rows;
    }
}
