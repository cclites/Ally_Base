<?php

namespace App\Console\Commands;

use App\Caregiver;
use App\Client;
use App\Shift;
use Illuminate\Console\Command;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\File;

class RetroactiveDecommissionLetters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:decommission_letters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Go back and retroactively create decommission letters for all users who did not get one from before the feature was created';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info( 'Starting the decommission letter generation..' );

        $caregivers = Caregiver::whereHas( 'documents', function( $q ){

            $q->where( 'description', '!=', 'Caregiver Deactivation Document' );
        })->orWhereDoesntHave( 'documents' )->inactive()->get();

        DB::beginTransaction();
        foreach( $caregivers as $c ){

            $c->load( 'deactivationReason' );

            $shifts = collect( Shift::where( 'caregiver_id', $c->id )->pluck( 'hours' )->all() );
            $totalLifetimeHours = $shifts->sum();
            $totalLifetimeShifts = $shifts->count();

            $pdf = PDF::loadView( 'business.caregivers.deactivation_reason', [ 'caregiver' => $c, 'deactivatedBy' => 'System Admin', 'totalLifetimeHours' => $totalLifetimeHours, 'totalLifetimeShifts' => $totalLifetimeShifts ]);

            $filePath = $this->generateUniqueDeactivationPdfFilename( $c->id );
            $this->info( "Creating File $filePath for Caregiver $c->name ( ID: $c->id )" );

            try {
                if ( $pdf->save( $filePath ) ) {

                    $c->documents()->create([

                        'filename'          => File::basename( $filePath ),
                        'original_filename' => File::basename( $filePath ),
                        'description'       => 'Caregiver Deactivation Document',
                        'user_id'           => $c->id
                    ]);

                    $this->info( "SUCCESS Creating File $filePath for Caregiver $c->name ( ID: $c->id )" );
                    DB::commit();
                    return true;
                } else {

                    $this->error( "ERROR Creating File $filePath for Caregiver $c->name ( ID: $c->id )" );
                    return false;
                }
            } catch ( \Exception $ex ) {

                $this->info( "ERROR Creating File $filePath for Caregiver $c->name ( ID: $c->id )" );
                DB::rollback();
                return false;
            }
        }

        $this->info( "Successfully finished generating caregiver files.." );

        $clients = Client::whereHas( 'documents', function( $q ){

            $q->where( 'description', '!=', 'Client Deactivation Document' );
        })->orWhereDoesntHave( 'documents' )->inactive()->get();

        DB::beginTransaction();
        foreach( $clients as $c ){

            $c->load( 'deactivationReason' );

            $shifts = collect( Shift::where( 'client_id', $c->id )->pluck( 'hours' )->all() );
            $totalLifetimeHours = $shifts->sum();
            $totalLifetimeShifts = $shifts->count();

            $pdf = PDF::loadView( 'business.clients.deactivation_reason', [ 'client' => $c, 'deactivatedBy' => 'System Admin', 'totalLifetimeHours' => $totalLifetimeHours, 'totalLifetimeShifts' => $totalLifetimeShifts ]);

            $filePath = $this->generateUniqueDeactivationPdfFilename( $c->id );
            $this->info( "Creating File $filePath for Client $c->name ( ID: $c->id )" );
            try {
                if ( $pdf->save( $filePath ) ) {

                    $c->documents()->create([

                        'filename'          => File::basename( $filePath ),
                        'original_filename' => File::basename( $filePath ),
                        'description'       => 'Client Deactivation Document',
                        'user_id'           => $c->id
                    ]);

                    $this->info( "SUCCESS Creating File $filePath for Client $c->name ( ID: $c->id )" );
                    DB::commit();
                    return true;
                } else {

                    $this->error( "ERROR Creating File $filePath for Client $c->name ( ID: $c->id )" );
                    return false;
                }
            } catch ( \Exception $ex ) {

                $this->info( "ERROR Creating File $filePath for Client $c->name ( ID: $c->id )" );
                DB::rollback();
                return false;
            }
        }

        $this->info( "Successfully finished generating client files.." );
    }

    private function generateUniqueDeactivationPdfFilename( $id ) : string
    {
        $dir = storage_path('app/documents/');
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 493, true);
        }

        for ($i = 1; $i < 500; $i ++) {
            $filename = str_slug( $id . '-' . 'deactivation-details-' . Carbon::now()->format( 'm-d-Y' ) );

            if ($i > 1) {
                $filename .= "_$i";
            }

            $filename .= '.pdf';

            if (! File::exists( $dir . $filename ) ) {
                break;
            }
        }

        return $dir . $filename;
    }
}
