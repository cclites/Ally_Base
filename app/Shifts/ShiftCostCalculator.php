<?php
namespace App\Shifts;

use App\Billing\BillingCalculator;
use App\Billing\Invoiceable\ShiftService;
use App\Payments\MileageExpenseCalculator;
use App\ShiftCostHistory;

class ShiftCostCalculator
{
    /**
     * @var \App\Billing\Payments\Methods\BankAccount|\App\Billing\Payments\Methods\CreditCard
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
    public function getAllyFee($expensesIncluded = true)
    {
        if ($this->hasPersistedCosts()) {
            return $this->getPersistedCosts()->ally_fee;
        }

        if ($this->isUsingClientRate()) {
            // New (February 2019)
            if ($this->shift->services->count()) {
                $shiftFee = $this->shift->services->reduce(function($carry, ShiftService $service) {
                    $amount = multiply($service->getClientRate() ?? 0, $service->duration);
                    $fee = ($this->paymentType)
                        ? $this->paymentType->getAllyFee($amount, true)
                        : $this->client->getAllyFee($amount, true);
                    return add($carry, $fee);
                }, 0.0);
            } else {
                $rate = ($this->paymentType)
                    ? $this->paymentType->getAllyFee($this->shift->getClientRate(), true)
                    : $this->client->getAllyFee($this->shift->getClientRate(), true);
                $shiftFee = ($this->shift->fixed_rates)
                    ? $rate
                    : multiply($rate, $this->shift->duration());
            }
        } else {
            // Old (Pre-February 2019)
            $hourlyRate = AllyFeeCalculator::getHourlyRate($this->client, $this->paymentType, $this->shift->caregiver_rate, $this->shift->provider_fee);

            if ($this->shift->fixed_rates) {
                // Still use getHourlyRate method for ease of use, but don't do any multiplication
                $shiftFee = $hourlyRate;
            }
            else {
                $hours = $this->shift->duration();
                $shiftFee = bcmul($hours, $hourlyRate, BillingCalculator::DEFAULT_SCALE);
            }
        }

        $expenses = $this->getCaregiverExpenses();
        $expenseFee = $expensesIncluded ? AllyFeeCalculator::getFee($this->client, $this->paymentType, $expenses) : 0;

        return add($shiftFee, $expenseFee);
    }

    public function getClientCost($expensesIncluded = true)
    {
        return $this->getTotalCost($expensesIncluded);
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

        // New (February 2019)
        if ($this->isUsingClientRate()) {
            $leftover = subtract($this->getClientCost(), $this->getCaregiverCost());
            $providerFee = subtract($leftover, $this->getAllyFee());
            return $providerFee;
        }

        // Old (Pre-February 2019)
        if ($this->shift->fixed_rates) {
            return $this->shift->provider_fee;
        }

        return round(
            bcmul($this->shift->duration(), $this->shift->provider_fee, BillingCalculator::DEFAULT_SCALE),
            BillingCalculator::DECIMAL_PLACES,
            BillingCalculator::ROUNDING_METHOD
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

        if ($this->shift->services->count()) {
            $shift = $this->sumServices('caregiver_rate');
        } else {
            if ($this->shift->fixed_rates) {
                $shift = $this->shift->caregiver_rate;
            } else {
                $shift = multiply($this->shift->duration(), $this->shift->caregiver_rate);
            }
        }

        $expenses = 0;
        if ($expensesIncluded) {
            $expenses = $this->getCaregiverExpenses();
        }

        return round(
            bcadd($shift, $expenses, BillingCalculator::DEFAULT_SCALE),
            BillingCalculator::DECIMAL_PLACES,
            BillingCalculator::ROUNDING_METHOD
        );
    }

    /**
     * Return the caregiver expenses (other expenses + mileage), no ally fee or hours
     */
    public function getCaregiverExpenses()
    {
        if ($this->hasPersistedCosts()) {
            return bcadd($this->getPersistedCosts()->caregiver_expenses, $this->getPersistedCosts()->caregiver_mileage, BillingCalculator::DECIMAL_PLACES);
        }

        $mileage = $this->mileageCalculator()->getCaregiverReimbursement();
        $expenses = bcadd($this->shift->other_expenses, $mileage, BillingCalculator::DEFAULT_SCALE);
        return round(
            $expenses,
            BillingCalculator::DECIMAL_PLACES,
            BillingCalculator::ROUNDING_METHOD
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
            bcadd($expenses, $fee, BillingCalculator::DEFAULT_SCALE),
            BillingCalculator::DECIMAL_PLACES,
            BillingCalculator::ROUNDING_METHOD
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
                    bcmul($this->getPersistedCosts()->caregiver_mileage, $this->getPersistedCosts()->ally_pct, BillingCalculator::DECIMAL_PLACES),
                    BillingCalculator::DECIMAL_PLACES
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
     * @param bool $expensesIncluded
     * @return float
     * @throws \Exception
     */
    public function getTotalCost($expensesIncluded = true)
    {
        // New (February 2019)
        if ($this->isUsingClientRate()) {
            if ($this->shift->services->count()) {
                $shiftTotal = $this->sumServices('client_rate');
            } else if ($this->shift->fixed_rates) {
                $shiftTotal = $this->shift->client_rate;
            } else {
                $shiftTotal = multiply($this->shift->duration(), $this->shift->client_rate);
            }

            $expenseTotal = $expensesIncluded ? add($this->getMileageCost(true), $this->getOtherExpenses(true)) : 0;
            return add($shiftTotal, $expenseTotal);
        }

        // Old (Pre-February 2019)
        return round(
            bcadd(
                bcadd($this->getProviderFee(), $this->getCaregiverCost($expensesIncluded), BillingCalculator::DEFAULT_SCALE),
                $this->getAllyFee(),
                BillingCalculator::DEFAULT_SCALE
            ),
            BillingCalculator::DECIMAL_PLACES,
            BillingCalculator::ROUNDING_METHOD
        );
    }

    public function getTotalRates():  Rates
    {
        return new Rates(
            $this->getCaregiverCost(true),
            $this->getProviderFee(),
            $this->getClientCost(),
            $this->getAllyFee(),
            true,
            $this->shift->fixed_rates
        );
    }

    public function getHourlyRates(): Rates
    {
        if ($this->shift->fixed_rates) {
            return $this->getTotalRates();
        }

        $hours = $this->getBillableUnits();

        if ($hours == 0) {
            return new Rates(0, 0, 0, 0, true, false);
        }

        return new Rates(
            divide($this->getCaregiverCost(false), $hours),
            divide($this->getProviderFee(), $hours),
            divide($this->getClientCost(false), $hours),
            divide($this->getAllyFee(false), $hours),
            true,
            false
        );
    }

    public function getBillableUnits(): float
    {
        if ($this->shift->fixed_rates) {
            return 1;
        }

        if ($this->shift->services->count()) {
            return $this->shift->services->reduce(function($carry, ShiftService $service) {
                return add($carry, $service->duration);
            }, 0.0);
        }

        return $this->shift->duration();
    }


    /**
     *  Get the total cost per hour of this shift
     *  @deprecated
     */
    public function getTotalHourlyCost()
    {
        return $this->shift->client_rate;
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

    protected function sumServices(string $field): float
    {
        return $this->shift->services->reduce(function($carry, ShiftService $service) use ($field) {
            $amount = multiply($service->{$field} ?? 0, $service->duration);
            return add($carry, $amount);
        }, 0.0);
    }

    protected function isUsingClientRate()
    {
        return $this->shift->client_rate !== null || $this->shift->services->count();
    }
}
