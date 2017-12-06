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


    public function __construct(Business $business, Carbon $startDate, Carbon $endDate, LoggerInterface $logger)
    {
        $this->logger = $logger;
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
        // Initialize Business Payment Class
        $businessPayment = new BusinessPaymentAggregator($this->business, $this->startDate, $this->endDate);

        // Separate Provider Clients
        $clientsUsingProviderPayment = $businessPayment->getClientsUsingProviderPayment();
        $clientsUsingProviderPaymentIds = $clientsUsingProviderPayment->pluck('id')->toArray();
        $clientsNotUsingProviderPayment = $this->business->clients()
            ->whereNotIn('id', $clientsUsingProviderPaymentIds)
            ->get();

        // Process Payments for Clients Not Using Provider Payment Method
        $count = 0;
        foreach($clientsNotUsingProviderPayment as $client) {
            $clientPayment = new ClientPaymentAggregator($client, $this->startDate, $this->endDate);
            try {
                $transaction = $clientPayment->charge();
                if (!$transaction) throw new PaymentMethodError("Unknown");
                $count++;
            }
            catch (\Exception $e) {
                $payment = $clientPayment->getPayment();
                $this->logger->warning('Failed charging ' . $payment->amount . ' to client ' . $client->name() . '(' . $client->id . ')');
            }
        }

        // Process Business Payment
        try {
            $transaction = $businessPayment->charge();
            if (!$transaction) throw new PaymentMethodError("Unknown");
            $count++;
        }
        catch (\Exception $e) {
            $payment = $businessPayment->getPayment();
            $this->logger->warning('Failed charging ' . $payment->amount . ' to business payment method for ' . $this->business->name . '(' . $this->business->id . ')');
        }

        return $count;
    }

    /**
     * Return an array of Payment models that will represent the actual charges
     */
    public function getPaymentModels()
    {
        // Initialize Business Payment Class
        $businessPayment = new BusinessPaymentAggregator($this->business, $this->startDate, $this->endDate);

        // Separate Provider Clients
        $clientsNotUsingProviderPayment = $this->business->clients()
            ->whereNotIn('id', $businessPayment->getClientIdsUsingProviderPayment())
            ->get();

        $payments = [];

        $payment = $businessPayment->getPayment();
        if ($payment->amount) {
            $payments[] = $payment;
        }

        // Process Payments for Clients Not Using Provider Payment Method
        foreach($clientsNotUsingProviderPayment as $client) {
            $clientPayment = new ClientPaymentAggregator($client, $this->startDate, $this->endDate);
            $payment = $clientPayment->getPayment();
            if ($payment->amount) {
                $payments[] = $payment;
            }
        }

        return $payments;
    }

    /**
     * Return an array of Payment models per client for reference
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