@extends('layouts.print')

@section('content')
    @include('layouts.partials.print_logo')
    <style>
        .mt {
            margin-top: 1.2rem;
        }

        .signature > svg {
            max-width: 50%;
        }
    </style>
    <div class="container">
        <div>
            <h3>{{ $rsa->client->business->name }} Client Referral Service Agreement</h3>
            <div class="row">
                <div class="col">
                    <p>This Agreement, effective this {{ $rsa->created_at->format('jS') }} day of {{ $rsa->created_at->format('M') }}, {{ $rsa->created_at->format('Y') }} (the “Effective Date”), is between
                        {{ $rsa->client->business->name }} LLC d/b/a Granny NANNIES, an independently owned and operated franchise location,
                        which is a licensed nurse registry under Florida Statutes §400.506 (hereinafter referred to as “Nurse
                        Registry”), located at 4045-A Tamiami Trail, Port Charlotte, FL 33952 and {{ $rsa->client->business->name }}
                        located at {{ $rsa->client->business->address1 }} City/State {{ $rsa->client->business->city }}/{{ $rsa->client->business->state }} Zip: {{ $rsa->client->business->zip }}
                        (hereinafter referred to as “Client”).</p>

                    <p>In consideration of the mutual promises and covenants of the parties herein contained, the parties hereby
                        agree and contract as follows:</p>

                    <ol>
                        <li>Nurse Registry, upon Client’s request, agrees to refer to Client a companion, certified nursing assistant
                            or home health aide who meets the criteria that Client provides (hereinafter referred to as “Nanny”). If a
                            Nanny that Nurse Registry refers to Client is not available or becomes ill, or for any other reason is
                            unable to provide services that Client needs, then, upon Client’s request, Nurse Registry will refer another
                            Nanny as soon as possible, without guarantee that another Nanny will be available.</li>


                        <li>The fees that Client will pay a Nanny for services that the Nanny provides for Client are separately
                            determined by Client and Nanny and are memorialized in a separate document for the benefit of those
                            parties. All fees for Nanny services are payable by Client directly to the Nanny. Separately, Client
                            agrees to pay Nurse Registry a separate referral fee for its referral services, which is described in
                            this Agreement and is payable by Client directly to Nurse Registry. Client will deliver its payments to
                            Nurse Registry and a referred Nanny pursuant to the payment arrangement described on Exhibit 1 hereto.

                            Client agrees to pay Nurse Registry the following fees for its services:

                            REFERRALS OF HOURLY NANNIES:
                            Client agrees to pay Nurse Registry a referral fee of $ {{ $rsa->referral_fee }} per hour for each hour of service that
                            an hourly Nanny referred by Nurse Registry provides for Client.

                            DRIVING WAIVER:
                            If a Nanny operates his/her own vehicle or Client’s vehicle in connection with the services that Nanny
                            provides for Client, that is a matter strictly between Client and Nanny, and Client hereby indemnifies,
                            holds harmless and releases Nurse Registry and its owners, officers, employees and representatives from
                            and against any liability, including legal defense fees and costs, for any and all damages, losses or
                            injuries (including death) to person or property (i) caused by or resulting from Contractor’s operation
                            of such vehicle, or (ii) involving such a vehicle that Contractor was operating at the time.

                            RETURNED CHECK POLICY:
                            It is agreed that a returned check issued to Nurse Registry will result in a $50.00 service charge per
                            check. Each account will be allowed two (2) returned checks after which payment by check will not be
                            accepted. All returned checks will be forwarded to the prosecutor’s office if the amount of the check
                            plus the service fee has not been paid within ten (10) business days of the non-payment.
                        </li>

                        <li>A referred Nanny is not an employee of Nurse Registry and will separately negotiate with Client all
                            aspects of the home-care arrangement, including but not limited to, the specific types of services to be
                            provided, the specific hours when services are to be provided, the location where the serviced are to be
                            provided, and whether any tools, materials or supplies are needed and, if so, which of such items will
                            be provided by the Client and Nanny, respectively.
                        </li>

                        <li>All fees for Nurse Registry’s referral services are payable by Client to Nurse Registry and are due
                            on or before the due date specified on the invoice, unless the hours worked are not reported to Nurse
                            Registry, in which case, notwithstanding anything herein or in any Exhibit hereto to the contrary, such
                            fees are due and fully payable to Nurse Registry on the last day of the second week following the week
                            in which the Nanny’s services were performed. Unpaid accounts will be subject to a late charge of 1.5%
                            per month (Annual Percentage Rate 18%) on unpaid balances or, if lower, the highest rate allowed by law.
                            Past due accounts will be automatically charged to the credit card on file that was used to secure the
                            account with for nonpayment after 10 days. Client agrees to pay all costs, including reasonable attorney
                            fees, in connection with Nurse Registry seeking unpaid amounts owed to the Nurse Registry, whether suit
                            be brought or not, if Client is in default hereunder.
                        </li>

                        <li>The rights and obligations of the parties hereunder are personal and Client may not assign any
                            rights hereunder. Nurse Registry may assign its rights only to its successor, including an entity that
                            acquires substantially all the business or assets of Nurse Registry.
                        </li>

                        <li>Client hereby agrees that as part of the consideration for which Nurse Registry agrees to refer
                            Nannies to Client, Client hereby agrees (i) to pay Nurse Registry Its referral fees as set forth in
                            Section 2 hereof with respect to any and all services that a Nanny referred by Nurse Registry provides
                            for Client during the term of this Agreement and the six (6) month period immediately following the
                            termination of this Agreement (the “Referral Fee Period”), and (ii) to ensure that all hours of services
                            that a Nanny referred by Nurse Registry provides for Client during the Referral Fee Period are reported
                            to Nurse Registry.
                        </li>

                        <li>Client hereby acknowledges and understands that Nurse Registry complies with Florida law
                            requirements that a licensed nurse registry (i) notify Client when it learns that a referred Nanny has
                            violated Chapter 400 of the Florida statutes, has violated other state laws, or has a deficiency in
                            professional credentials, and (ii) suggest that the Client consider terminating the Nanny; but Client
                            understands and acknowledges that Nurse Registry has no right to terminate or otherwise interfere with a
                            Client’s home-care relationship with a Nanny. Only Client and the Nanny have a right to make any changes
                            to, or terminate, their home-care relationship.
                        </li>
                        <li>Client hereby acknowledges that a Nanny referred by Nurse Registry is an independent contractor and
                            that Nurse Registry has no right or obligation to, directly or indirectly, monitor, supervise, manage,
                            train or exercise any control over the means and methods by which a Nanny provides services for Client,
                            and has no right to terminate, or in any other way interfere with, a Nanny’s relationship with Client.
                            Client acknowledges that Nurse Registry shall have no liability for any acts or omissions of a Nanny and
                            that a Nanny shall be solely and independently responsible for Nanny’s own acts or omissions while
                            providing services for Client.
                        </li>

                        <li>Either party hereto may terminate this Agreement by providing the other party with written notice at
                            least <span>{{ $rsa->termination_notice }}</span> prior to the date of termination.
                        </li>

                        <li>This Agreement shall constitute the entire agreement between the parties hereto, and all prior
                            agreements and any prior understandings or representations of any kind shall be superseded by the terms
                            of this Agreement. Any modification of this Agreement shall be binding only if evidenced in writing and
                            signed by each party.
                        </li>

                        <li>Client understands that Nurse Registry, as required by applicable law, has access to Registered
                            Nurses (RNs), who can provide an assessment of Client for an additional fee. Such RNs are available for
                            referral at Client’s request. The RNs generally charge a fixed fee for an assessment of $<span>{{ number_format($rsa->per_visit_assessment_fee, 2) }}</span>
                            per visit, although Client and a referred RN always remain free to negotiate a different fee. Nurse
                            Registry’s referral fee for referring an RN is $<span>{{ number_format($rsa->per_visit_referral_fee, 2) }}</span> per visit. The RN’s fee is payable by
                            Client to the RN directly; and the Nurse Registry’s fee is payable by Client to Nurse Registry directly.
                        </li>

                        <li>This Agreement shall be governed by, construed, and enforced in accordance with the laws of the
                            state of Florida. Any notice required or permitted under this Agreement shall be in writing and sent to
                            the other party by first class mail at the address first set forth above, or to such address as a party
                            hereto may specify in writing.
                            <ul style="list-style-type:none">
                                <li>A. Complaints. To report a complaint regarding the services you receive, please call toll-free 1-888-419-3456.</li>
                                <li>B. Abusive, neglectful, or exploitative practices. To report abuse, neglect, or exploitation, please call toll-free 1-800-962-2873.</li>
                                <li>C. Medicaid fraud. To report suspected Medicaid fraud, please call toll-free 1-866-966-7226. Medicaid fraud means an intentional deception or misrepresentation made by a health care provider with the knowledge that the deception could result in some unauthorized benefit to him or herself or some other person. It includes any act that constitutes fraud under federal or state law related to Medicaid.</li>
                            </ul>
                        </li>
                    </ol>
                    <div>IN WITNESS WHEREOF, the parties hereto have executed this Agreement on the day and year first
                        written above.
                    </div>

                    <div>
                        <div>GRANNY NANNIES</div>
                        <div>Executed By: {{ ucfirst($rsa->executed_by) }}</div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div>
                                @php
                                    $signatureOne = str_replace('width="800"', 'width="300"', $rsa->signature_one);
                                    $signatureOne = str_replace('height="300"', 'height="112"', $signatureOne);
                                @endphp
                                <div>By {{ $rsa->signature_one_text }}</div>
                                <div class="signature">{!! $signatureOne !!}</div>
                            </div>
                            <div>{{ $rsa->created_at->format('m/d/Y') }}</div>
                        </div>
                        <div class="col">
                            <div>
                                @php
                                    $signatureTwo = str_replace('width="800"', 'width="300"', $rsa->signature_two);
                                    $signatureTwo = str_replace('height="300"', 'height="112"', $signatureTwo);
                                @endphp
                                <div>By {{ $rsa->signature_two_text }}</div>
                                <div class="signature">{!! $signatureTwo !!}</div>
                            </div>
                            <div>{{ $rsa->created_at->format('m/d/Y') }}</div>
                        </div>
                    </div>

                    <hr>
                    <div class="row mt">
                        <div class="col">
                            <p>Exhibit 1</p>
                            <p>Client hereby elects the following method for delivering its payments to Nanny and to Nurse
                                Registry.</p>
                            @if (collect($rsa->payment_options)->contains(1))
                                    <div>Option 1: Check. Client will pay Nanny and Nurse Registry their respective fees by separate check to each.</div>
                            @elseif(collect($rsa->payment_options)->contains(2))
                                <div>Option 2: Payment Processing Firm.  Client, or a third-party payor that Client designates (e.g., an insurance company), will pay Nanny and Nurse Registry their respective fees through a payment processing firm that Client will separately engage, which will function as an agent of Client.
                                    Client understands and agrees that Client is unconditionally liable for amounts it owes to Nanny and Nurse Registry with respect to services that Nanny provides for Client.
                                    Under Option 2, The payment processing firm will bill the person that Client designates as the person responsible for paying Nanny and Nurse Registry their respective fees on Client’s behalf; and the payment processing firm will collect the amounts paid by the responsible person and disburse the appropriate portions of such payments to the Nanny and to Nurse Registry on behalf of, and as an agent of, Client.  If the payment processing firm fails to remit payment of Nurse Registry’s fee in full to Nurse Registry within ninety (90) days, then Client agrees to remit payment of such fee to Nurse Registry immediately. Client understands that Client remains responsible for paying all fees, regardless of any third-party payor and any payment processing firm.</div>
                            @endif
                            <p>IN WITNESS THEREOF, the Client has executed this Exhibit 1on the day and year set forth
                                below.
                                CLIENT</p>
                            <div class="row">
                                <div class="col">
                                    <div>
                                        @php
                                            $clientSignature = str_replace('width="800"', 'width="300"', $rsa->signature_client);
                                            $clientSignature = str_replace('height="300"', 'height="112"', $clientSignature);
                                        @endphp
                                        <div>By {{ $rsa->signature_client_text }}</div>
                                        <div class="signature">{!! $clientSignature !!}</div>
                                    </div>
                                    <div>{{ $rsa->created_at->format('m/d/Y') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt">
                        {{ $rsa->created_at->format('m/d/Y') }} - {{ $rsa->executed_by_ip }}
                    </div>
                </div>
            </div>
        </div>
@endsection
