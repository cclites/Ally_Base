<?php
namespace App\Payments;

use App\Business;
use App\Exceptions\PaymentMethodError;
use Carbon\Carbon;
use Psr\Log\LoggerInterface;

class PaymentProcessor
{
    /**
     * @var \App\Business
     */
    protected $business;

    /**
     * All processed shift IDs
     *
     * @var array
     */
    protected $shifts = [];

    /**
     * Shift ids belonging to failed caregiver deposits
     *
     * @var array
     */
    protected $failedCaregiverShifts = [];

    /**
     * Shift ids belonging to failed business deposits
     *
     * @var array
     */
    protected $failedBusinessShifts = [];

    /**
     * @var \Carbon\Carbon
     */
    private $startDate;

    /**
     * @var \Carbon\Carbon
     */
    private $endDate;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;


    public function __construct(Business $business, Carbon $startDate, Carbon $endDate, LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: logger();
        $this->business = $business;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Process all the payments relating to the business (returns an integer of successful transactions)
     *
     * @return int
     */
    public function process()
    {
        $aggregators = $this->getAggregators();

        $count = 0;
        foreach($aggregators as $aggregator) {
            try {
                $transaction = $aggregator->charge();
                if (!$transaction) throw new PaymentMethodError("Unknown");
                $count++;
            }
            catch (\Exception $e) {
                $payment = $aggregator->getPayment();
                if ($payment->client) {
                    $type = 'client';
                    $name = $payment->client->name();
                }
                else {
                    $type = 'business';
                    $name = $payment->business->name;
                }
                $this->logger->warning('Failed charging ' . $payment->amount . ' to ' . $type . ' ' . $name);
            }
        }

        return $count;
    }

    /**
     * Return an array of Payment models that will represent the actual charges
     */
    public function getPaymentModels()
    {
        $aggregators = $this->getAggregators();

        return array_map(function($aggregator) {
            return $aggregator->getPayment();
        }, $aggregators);
    }

    /**
     * Prepare the aggregator instances for the business and clients
     *
     * @return \App\Contracts\PaymentAggregatorInterface[]
     */
    protected function getAggregators()
    {
        // Initialize Business Payment Class
        $businessPayment = new BusinessPaymentAggregator($this->business, $this->startDate, $this->endDate);

        // Get Clients NOT using provider pay
        $clientsNotUsingProviderPayment = $businessPayment->getClientsNotUsingProviderPayment();

        // Initialize aggregators array (used for return)
        $aggregators = [];

        // Add business aggregator as long as business is not on hold
        if (!$this->business->isOnHold()) {
            $aggregators[] = $businessPayment;
        }

        foreach($clientsNotUsingProviderPayment as $client) {
             $aggregators[] = new ClientPaymentAggregator($client, $this->startDate, $this->endDate);
        }

        return array_filter($aggregators, function($aggregator) {
            if ($payment = $aggregator->getPayment()) {
                return $payment->amount > 0;
            }
            return false;
        });
    }

    /**
     * Return an array of Payment models per client (for reference only)
     *
     * @return array
     */
    public function getPaymentDataPerClient()
    {
        $payments = [];
        foreach($this->business->clients as $client) {
            $clientPayment = new ClientPaymentAggregator($client, $this->startDate, $this->endDate);
            $payment = $clientPayment->getPayment();
            if ($payment->amount) {
                // Add shift details and payment method
                $payment->total_shifts = $clientPayment->getAllPendingShifts()->count();
                $payment->unauthorized_shifts = $payment->total_shifts - $clientPayment->getShifts()->count();
                $payments[] = $payment;
            }
        }

        return $payments;
    }

}