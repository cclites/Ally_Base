<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ClientOnboarding extends Model
{
    protected $guarded = ['id'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function activities()
    {
        return $this->belongsToMany(OnboardingActivity::class, 'client_onboarding_activities')
            ->withPivot('assistance_level');
    }

    public function signature()
    {
        return $this->morphOne(Signature::class, 'signable');
    }

    public function createIntakePdf()
    {
        $this->load('client', 'signature', 'activities');
        $pdf = PDF::loadView('business.clients.onboarding_doc', ['onboarding' => $this]);
        $dir = storage_path('app/documents/');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 493, true);
        }
        $filename = str_slug($this->client->id . ' ' . $this->client->name.' Intake').'.pdf';
        $filePath = $dir . '/' . $filename;
        if (config('app.env') == 'local') {
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }
        $response = $pdf->save($filePath);

        if ($response) {
            DB::transaction(function() use ($response, $filePath) {
                $this->update(['intake_pdf' => str_after($filePath, 'storage/')]);
                $this->client->documents()->create([
                    'filename' => File::basename($filePath),
                    'original_filename' => File::basename($filePath),
                    'description' => 'Client Intake Document'
                ]);
            });
        }
    }
}
