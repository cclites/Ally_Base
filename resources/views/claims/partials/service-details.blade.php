<?php
/**
 * @var \App\Claims\ClaimInvoice $claim The ClaimInvoice being printed
 * @var \App\Business $sender The related Business
 * @var \App\Billing\Payer $recipient The related Payer
 * @var \App\Client|null $client The client model related to the claim
 * @var \App\Claims\ClaimInvoiceItem $item The current claim item
 * @var \App\Claims\ClaimableService $service The current claimable service
 */
?>
<div class="container-fluid" style="margin-top: 1rem;">
    <div class="row mb-3">
        <div class="col-sm-6">
            <div><strong>Client</strong></div>
            {{ $item->getClientName() }}
        </div>
        <div class="col-sm-6">
            <div><strong>Caregiver</strong></div>
            {{ $item->getCaregiverName() }}
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-sm-6">
            <div><strong>Service Name and Code</strong></div>
            {{ $service->getName() }}
        </div>
        <div class="col-sm-6">
            <div><strong>Duration</strong></div>
            {{ number_format($service->getDuration(), 2) }} Hours
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-sm-6">
            <div><strong>Service Start Date & Time</strong></div>
            {{ $service->getStartTime()->setTimezone($claim->business->getTimezone())->format('m/d/Y g:i A') }}
        </div>
        <div class="col-sm-6">
            <div><strong>Service End Date & Time</strong></div>
            {{ $service->getEndTime()->setTimezone($claim->business->getTimezone())->format('m/d/Y g:i A') }}
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-sm-6">
            <div><strong>Special Designation (OT/Reg)</strong></div>
            @if($service->is_overtime)
                Overtime/Holiday
            @else
                Regular
            @endif
        </div>
        <div class="col-sm-6">
            <div><strong>Caregiver Comments and Notes</strong></div>
            {!! $service->caregiver_comments ? nl2br($service->caregiver_comments) : 'None' !!}
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-sm-12">
            <div><strong>Activities Performed</strong></div>
            @if($service->getActivities()->count() == 0)
                None
            @else
                <table class="table">
                    <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($service->getActivities() as $activity)
                        <tr>
                            <td>{{ $activity->code }}</td>
                            <td>{{ $activity->name }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif

        </div>
    </div>
    <div class="row mb-3">
        <div class="col-sm-6">
            <div><strong>Was this shift electronically verified?</strong></div>
            <div>{{ $service->getHasEvv() ? 'Yes' : 'No' }}</div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-sm-6">
                    <div class="mb-2"><strong>Clock In EVV Data</strong></div>
                    <table class="table">
                        @if($service->evv_method_in == \App\Claims\ClaimableService::EVV_METHOD_TELEPHONY)
                            <tr>
                                <th>Verification Method</th>
                                <td>Telephony</td>
                            </tr>
                            <tr>
                                <th>Phone Number</th>
                                <td>{{ $service->checked_in_number }}</td>
                            </tr>
                        @elseif($service->evv_method_in == \App\Claims\ClaimableService::EVV_METHOD_GEOLOCATION)
                            <tr>
                                <th>Verification Method</th>
                                <td>Geolocation</td>
                            </tr>
                            <tr>
                                <th>Date & Time</th>
                                <td>{{ $service->evv_start_time->setTimezone($claim->business->getTimezone())->format('m/d/Y g:i A') }}</td>
                            </tr>
                            <tr>
                                <th>Latitude</th>
                                <td>{{ $service->checked_in_latitude }}</td>
                            </tr>
                            <tr>
                                <th>Longitude</th>
                                <td>{{ $service->checked_in_longitude }}</td>
                            </tr>
                            <tr>
                                <th>Distance (Miles)</th>
                                <td>{{ $service->getCheckedInDistance() }}m</td>
                            </tr>
                        @else
                            <tr><td colspan="2">None</td></tr>
                        @endif
                    </table>
                </div>
                <div class="col-sm-6">
                    <div class="mb-2"><strong>Clock Out EVV Data</strong></div>
                    <table class="table">
                        @if($service->evv_method_out == \App\Claims\ClaimableService::EVV_METHOD_TELEPHONY)
                            <tr>
                                <th>Verification Method</th>
                                <td>Telephony</td>
                            </tr>
                            <tr>
                                <th>Phone Number</th>
                                <td>{{ $service->checked_out_number }}</td>
                            </tr>
                        @elseif($service->evv_method_out == \App\Claims\ClaimableService::EVV_METHOD_GEOLOCATION)
                            <tbody>
                            <tr>
                                <th>Verification Method</th>
                                <td>Geolocation</td>
                            </tr>
                            <tr>
                                <th>Date & Time</th>
                                <td>{{ $service->evv_end_time->setTimezone($claim->business->getTimezone())->format('m/d/Y g:i A') }}</td>
                            </tr>
                            <tr>
                                <th>Latitude</th>
                                <td>{{ $service->checked_out_latitude }}</td>
                            </tr>
                            <tr>
                                <th>Longitude</th>
                                <td>{{ $service->checked_out_longitude }}</td>
                            </tr>
                            <tr>
                                <th>Distance (Miles)</th>
                                <td>{{ $service->getCheckedOutDistance() }}m</td>
                            </tr>
                            </tbody>
                        @else
                            <tr><td colspan="2">None</td></tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
    @if($service->clientSignature || $service->caregiverSignature)
    <div class="row mb-3">
        @if($service->clientSignature)
            <div class="col-sm-6">
                <div><strong>Client Signature</strong></div>
                <div class="signature">
                    {!! $service->clientSignature->content !!}
                </div>
            </div>
        @endif
        @if($service->caregiverSignature)
            <div class="col-sm-6">
                <div><strong>Caregiver Signature</strong></div>
                <div class="signature">
                    {!! $service->caregiverSignature->content !!}
                </div>
            </div>
        @endif
    </div>
    @endif
</div>