<?php
namespace App\Shifts;

use App\Payments\MileageExpenseCalculator;

class CostCalculator
{
    /**
     * Number of decimals to use in bcmath calculations
     */
    const DEFAULT_SCALE = 4;

    /**
     * Number of decimals to use in rounding
     */
    const DECIMAL_PLACES = 2;

    /**
     * Rounding methodology
     */
    const ROUNDING_METHOD = PHP_ROUND_HALF_UP;

    /**
     * @var \App\BankAccount|\App\CreditCard
     */
    protected $paymentType;

    /**
     * @var \App\Client
     */
    protected $client;

    /**
     * @var \App\Shift
     */
    protected $shift;

    public function __construct($shift)
    {
        $this->shift = $shift;
        $this->client = $this->shift->client;
        if (!$this->client) throw new \Exception('Shift does not have a client, cannot calculate costs.');
    }

    /**
     * Define a different payment method other than the default
     *
     * @param $method
     * @return $this
     */
    public function setPaymentType($method)
    {
        $this->paymentType = $method;
        return $this;
    }

    /**
     * Return the total ally allotments accounting for expense reimbursements
     *
     * @return float
     */
    public function getAllyFee()
    {
        $hours = $this->shift->duration();
        $hourlyRate = AllyFeeCalculator::getHourlyRate($this->client, $this->paymentType, $this->shift->caregiver_rate, $this->shift->provider_fee);
        $shiftFee = bcmul($hours, $hourlyRate, self::DEFAULT_SCALE);

        $expenses = $this->getCaregiverExpenses();
        $expenseFee = AllyFeeCalculator::getFee($this->client, $this->paymentType, $expenses);

        return round(
            bcadd($shiftFee, $expenseFee, self::DEFAULT_SCALE),
            self::DECIMAL_PLACES,
            self::ROUNDING_METHOD
        );
    }

    /**
     * Return the provider allotments for the shift (no ally fee or expenses)
     *
     * @return float
     */
    public function getProviderFee()
    {
        return round(
            bcmul($this->shift->duration(), $this->shift->provider_fee, self::DEFAULT_SCALE),
            self::DECIMAL_PLACES,
            self::ROUNDING_METHOD
        );
    }

    /**
     * Return the caregiver allotments with expense reimbursements (no ally fee)
     *
     * @return float
     */
    public function getCaregiverCost()
    {
        $shift = bcmul($this->shift->duration(), $this->shift->caregiver_rate, self::DEFAULT_SCALE);
        $expenses = $this->getCaregiverExpenses();

        return round(
            bcadd($shift, $expenses, self::DEFAULT_SCALE),
            self::DECIMAL_PLACES,
            self::ROUNDING_METHOD
        );
    }

    /**
     * Return the caregiver expenses (other expenses + mileage), no ally fee or hours
     */
    public function getCaregiverExpenses()
    {
        $mileage = $this->mileageCalculator()->getCaregiverReimbursement();
        $expenses = bcadd($this->shift->other_expenses, $mileage, self::DEFAULT_SCALE);
        return round(
            $expenses,
            self::DECIMAL_PLACES,
            self::ROUNDING_METHOD
        );
    }

    /**
     * Return the other expenses with the ally fee included
     */
    public function getOtherExpenses()
    {
        $expenses = $this->shift->other_expenses;
        $fee = AllyFeeCalculator::getFee($this->client, $this->paymentType, $this->shift->other_expenses);
        return round(
            bcadd($expenses, $fee, self::DEFAULT_SCALE),
            self::DECIMAL_PLACES,
            self::ROUNDING_METHOD
        );
    }

    /**
     * Return the mileage costs with the ally fee included
     *
     * @return string
     */
    public function getMileageCost()
    {
        return $this->mileageCalculator()->getTotalCost();
    }

    /**
     * Return an instance of the Mileage Expense Calculator class
     *
     * @return \App\Payments\MileageExpenseCalculator
     */
    public function mileageCalculator()
    {
        return new MileageExpenseCalculator($this->client, $this->shift->business, $this->paymentType, $this->shift->mileage);
    }

    /**
     * Return the total cost of a shift with all expenses included (amount owed by client)
     *
     * @return float
     */
    public function getTotalCost()
    {
        return round(
            bcadd(
                bcadd($this->getProviderFee(), $this->getCaregiverCost(), self::DEFAULT_SCALE),
                $this->getAllyFee(),
                self::DEFAULT_SCALE
            ),
            self::DECIMAL_PLACES,
            self::ROUNDING_METHOD
        );
    }
}