<?php
namespace App\Shifts;

use App\Payments\MileageExpenseCalculator;
use App\ShiftCostHistory;

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

    /**
     * @var bool|null
     */
    protected $hasCostsPersisted = null;

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
     * @throws \Exception
     */
    public function getAllyFee()
    {
        if ($this->hasPersistedCosts()) {
            return $this->getPersistedCosts()->ally_fee;
        }

        $hourlyRate = AllyFeeCalculator::getHourlyRate($this->client, $this->paymentType, $this->shift->caregiver_rate, $this->shift->provider_fee);

        if ($this->shift->fixed_rates) {
            // Still use getHourlyRate method for ease of use, but don't do any multiplication
            $shiftFee = $hourlyRate;
        }
        else {
            $hours = $this->shift->duration();
            $shiftFee = bcmul($hours, $hourlyRate, self::DEFAULT_SCALE);
        }

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
        if ($this->hasPersistedCosts()) {
            return $this->getPersistedCosts()->provider_fee;
        }

        if ($this->shift->fixed_rates) {
            return $this->shift->provider_fee;
        }

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
    public function getCaregiverCost($expensesIncluded = true)
    {
        if ($this->hasPersistedCosts()) {
            if ($expensesIncluded) {
                return $this->getPersistedCosts()->caregiver_total;
            }
            return $this->getPersistedCosts()->caregiver_shift;
        }

        if ($this->shift->fixed_rates) {
            $shift = $this->shift->caregiver_rate;
        }
        else {
            $shift = bcmul($this->shift->duration(), $this->shift->caregiver_rate, self::DEFAULT_SCALE);
        }

        $expenses = 0;
        if ($expensesIncluded) {
            $expenses = $this->getCaregiverExpenses();
        }

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
        if ($this->hasPersistedCosts()) {
            return bcadd($this->getPersistedCosts()->caregiver_expenses, $this->getPersistedCosts()->caregiver_mileage, self::DECIMAL_PLACES);
        }

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
    public function getOtherExpenses($allyFeeIncluded = true)
    {
        if ($this->hasPersistedCosts() && !$allyFeeIncluded) {
            return $this->getPersistedCosts()->caregiver_expenses;
        }

        $expenses = $this->shift->other_expenses;
        $fee = 0;
        if ($allyFeeIncluded) {
            $fee = AllyFeeCalculator::getFee($this->client, $this->paymentType, $this->shift->other_expenses);
        }

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
    public function getMileageCost($allyFeeIncluded = true)
    {
        if ($this->hasPersistedCosts()) {
            if ($allyFeeIncluded) {
                return bcadd(
                    $this->getPersistedCosts()->caregiver_mileage,
                    bcmul($this->getPersistedCosts()->caregiver_mileage, $this->getPersistedCosts()->ally_pct, self::DECIMAL_PLACES),
                    self::DECIMAL_PLACES
                );
            }
            return $this->getPersistedCosts()->caregiver_mileage;
        }

        if ($allyFeeIncluded) {
            return $this->mileageCalculator()->getTotalCost();
        }
        return $this->mileageCalculator()->getCaregiverReimbursement();
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

    /**
     *  Get the total cost per hour of this shift
     */
    public function getTotalHourlyCost()
    {
        $allyFee = AllyFeeCalculator::getHourlyRate($this->client, $this->paymentType, $this->shift->caregiver_rate, $this->shift->provider_fee);
        return round(
            bcadd(
                bcadd($this->shift->caregiver_rate, $this->shift->provider_fee, 4),
                $allyFee,
                4
            ), 2
        );
    }

    /**
     * @return bool
     */
    public function hasPersistedCosts()
    {
        return ($this->hasCostsPersisted !== null)
            ? $this->hasCostsPersisted
            : $this->hasCostsPersisted = !!$this->shift->costHistory;
    }

    /**
     * @return ShiftCostHistory|null
     */
    public function getPersistedCosts()
    {
        return $this->shift->costHistory;
    }

    /**
     * @return bool
     */
    public function persist()
    {
        // Ensure values are calculated, not pulled from existing shift_costs record
        $this->hasCostsPersisted = false;

        $costs = ShiftCostHistory::findOrNew($this->shift->id);
        $costs->fill([
            'caregiver_shift' => $this->getCaregiverCost(false),
            'caregiver_mileage' => $this->getMileageCost(false),
            'caregiver_expenses' => $this->getOtherExpenses(false),
            'caregiver_total' => $this->getCaregiverCost(true),
            'provider_fee' => $this->getProviderFee(),
            'ally_fee' => $this->getAllyFee(),
            'total_cost' => $this->getTotalCost(),
            'ally_pct' => AllyFeeCalculator::getPercentage($this->client, $this->paymentType),
        ]);
        if ($this->shift->costHistory()->save($costs)) {
            $this->shift->load('costHistory');
            return true;
        }
        return false;
    }
}
