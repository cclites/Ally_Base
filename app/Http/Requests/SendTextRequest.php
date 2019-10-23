<?php
namespace App\Http\Requests;

use App\Caregiver;
use App\PhoneNumber;
use App\Services\PhoneService;
use Illuminate\Support\Collection;

class SendTextRequest extends BusinessRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'message' => 'required|string|min:5|max:' . PhoneService::MAX_MESSAGE_LENGTH,
            'recipients' => 'array',
            'recipients.*' => 'integer',
            'can_reply' => 'boolean',
        ];
    }

    /**
     * Scrub the requested list of recipients to those that are
     * for the requested business, are active and have a SMS-enabled
     * phone number stored.  If no recipient IDs are present in the
     * request, all eligible Caregivers will be returned.
     *
     * @return Collection
     */
    public function getEligibleCaregivers() : Collection
    {
        $query = Caregiver::with('phoneNumbers')
            ->forRequestedBusinesses()
            ->active()
            ->whereHas('phoneNumbers', function ($q) {
                $q->where('receives_sms', 1);
            });

        if (filled($this->input('recipients'))) {
            $query->whereIn('id', $this->input('recipients'));
        }

        return $query->get()->toBase();
    }

    /**
     * Get the outgoing SMS number for the current business.
     * Returns default twilio number if no number set and
     * Returns null if no number set and replies are turned on.
     *
     * @return string|null
     */
    public function getOutgoingNumber() : ?string
    {
        if ($from = $this->getBusiness()->outgoing_sms_number) {
            return $from;
        }

        if ($this->input('can_reply')) {
            // Cannot proceed
            return null;
        }

        return PhoneNumber::formatNational(config('services.twilio.default_number'));
    }
}
