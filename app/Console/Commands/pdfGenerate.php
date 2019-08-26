<?php


namespace App\Console\Commands;


use Illuminate\Console\Command;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Illuminate\Support\Facades\File;
use App\Client;


class pdfGenerate extends Command
{
    protected $signature = 'pdfGen';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $client = Client::where('id', 157)->with([
            'user',
            'creator',
            'medications',
            'notes.creator',
            'careDetails',
            'carePlans',
            'caseManager',
            'business',
            'skilledNursingPoc',
            'goals',
            'notes' => function ($query) {
                return $query->orderBy('created_at', 'desc');
            },
            'contacts',
        ])->get();

        $pdf = PDF::loadView('poc.poc', compact('client'));

        $dir = storage_path('app/documents/');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 493, true);
        }

        $filename = str_slug('Test').'.pdf';

        $filePath = $dir . '/' . $filename;
        File::delete($filePath);

        $response = $pdf->save($filePath);
    }

}