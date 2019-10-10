<?php

namespace App\Http\Resources;

use App\Audit;
use App\Billing\Payments\Methods\BankAccount;
use App\Billing\Payments\Methods\CreditCard;
use App\Business;
use App\Caregiver;
use App\Client;
use App\ClientMedication;
use App\Schedule;
use App\User;
use Illuminate\Http\Resources\Json\Resource;

class AuditLogResource extends Resource
{
    /** @var Audit */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'auditable_id' => $this->resource->auditable_id,
            'auditable_title' => $this->resource->auditable_title,
            'auditable_type' => $this->resource->auditable_type,
            'created_at' => $this->resource->created_at,
//            'diff' => $this->resource->diff,
            'event' => $this->resource->event,
            'id' => $this->resource->id,
//            'ip_address' => $this->resource->ip_address,
            'new_values' => $this->scrubEncryptedData($this->resource->auditable_type, $this->resource->new_values),
            'old_values' => $this->scrubEncryptedData($this->resource->auditable_type, $this->resource->old_values),
            'tags' => $this->resource->tags,
            'url' => $this->resource->url,
            'user' => [
                'id' => optional($this->resource->user)->id,
                'name' => optional($this->resource->user)->name,
            ],
//            'user_agent' => $this->resource->user_agent,
            'user_id' => $this->resource->user_id,
            'user_type' => $this->resource->user_type,
        ];
    }

    /**
     * Scrub encrypted data and values that should be hidden.
     *
     * @param string $modelClass
     * @param array|null $data
     * @return array
     */
    public function scrubEncryptedData(string $modelClass, ?array $data) : array
    {
        if (empty($data)) {
            return [];
        }

        $hiddenAttributes = collect([]);
        switch ($modelClass) {
            case (new User)->getMorphClass():
            case (new Client)->getMorphClass():
            case (new Caregiver)->getMorphClass():
                $hiddenAttributes = collect(['ssn']);
                break;
            case (new Schedule)->getMorphClass():
                $hiddenAttributes = collect([]);
                break;
            case (new Business)->getMorphClass():
                $hiddenAttributes = collect(['hha_password', 'tellus_password']);
                break;
            case (new BankAccount)->getMorphClass():
                $hiddenAttributes = collect(['routing_number', 'account_number']);
                break;
            case (new CreditCard)->getMorphClass():
                $hiddenAttributes = collect(['number']);
                break;
            case (new ClientMedication)->getMorphClass():
                $hiddenAttributes = collect(['type', 'dose', 'frequency', 'description', 'side_effects', 'notes', 'tracking', 'route', 'new_changed']);
                break;
        }

        return collect($data)->mapWithKeys(function ($item, $key) use ($hiddenAttributes) {
            if ($hiddenAttributes->contains($key)) {
                return [$key => '(hidden)'];
            }
            return [$key => $item];
        })
            ->toArray();
    }
}