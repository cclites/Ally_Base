<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

/**
 * App\ClientReferralServiceAgreement
 *
 * @property-read \App\Client $client
 * @mixin \Eloquent
 */
class ClientReferralServiceAgreement extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['payment_options' => 'array'];

    protected $dates = ['created_at', 'updated_at'];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($rsa) {
            $rsa->createPdf();
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function createPdf()
    {
        $this->load('client.business');
        $pdf = PDF::loadView('business.clients.service_referral_agreement_doc', ['rsa' => $this]);
        $dir = storage_path('app/documents/');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 493, true);
        }
        $filename = str_slug($this->client->id . ' ' . $this->client->name.' Referral Service Agreement').'.pdf';
        $filePath = $dir . '/' . $filename;
        if (config('app.env') == 'local') {
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }
        $response = $pdf->save($filePath);

        if ($response) {
            DB::transaction(function() use ($response, $filePath) {
                $this->update(['agreement_file' => str_after($filePath, 'storage/')]);
                $this->client->documents()->create([
                    'filename' => File::basename($filePath),
                    'original_filename' => File::basename($filePath),
                    'description' => 'Client Referral Service Agreement'
                ]);
            });
        }
    }

}
