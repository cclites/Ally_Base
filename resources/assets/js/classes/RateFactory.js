import moment from "moment";
import {Decimal} from 'decimal.js';

/**
 * RateFactory class for calculating rates
 *
 * Notes:
 *
 * allyFeeIncluded is a boolean used to designate whether or not the given clientRate includes the ally fee.  Shift expenses usually do not include the fee.
 */
class RateFactory {

    static getAllyFee(allyPct, clientRate, allyFeeIncluded = true)
    {
        if (! clientRate) {
            return 0;
        }
        clientRate = new Decimal(clientRate);
        allyPct = new Decimal(allyPct);  // Ex: 0.05

        return allyFeeIncluded
            ? clientRate.div(allyPct.add(1)).mul(allyPct)
            : clientRate.mul(allyPct);
    }

    static getProviderFee(clientRate, caregiverRate, allyPct, allyFeeIncluded = true)
    {
        if (! clientRate) {
            return 0;
        }
        clientRate = new Decimal(clientRate || 0);
        caregiverRate = new Decimal(caregiverRate || 0);
        let allyFee = this.getAllyFee(allyPct, clientRate, allyFeeIncluded);
        let providerFee = clientRate.sub(caregiverRate).sub(allyFee);

        return (providerFee.toFixed(2)) === "-0.00" ? 0 : providerFee;
    }

    static getClientRate(providerFee, caregiverRate, allyPct, allyFeeIncluded = true)
    {
        providerFee = new Decimal(providerFee || 0);
        caregiverRate = new Decimal(caregiverRate || 0);
        let allyFee = this.getAllyFee(allyPct, caregiverRate.add(providerFee), false);

        return providerFee.plus(caregiverRate).plus(allyFeeIncluded ? allyFee : 0);
    }

    static findMatchingRate(ratesArray, effectiveDate, serviceId, payerId, caregiverId, fixedRates = false)
    {
        effectiveDate = moment(effectiveDate, 'MM/DD/YYYY').format('YYYY-MM-DD');
        let effectiveRates = ratesArray.filter(item => {
            return item.effective_start <= effectiveDate
                && item.effective_end >=effectiveDate;
        });

        const search = (serviceId, payerId, caregiverId) => {
            return item => {
                return item.service_id === serviceId
                    && item.payer_id === payerId
                    && item.caregiver_id === caregiverId;
            }
        };

        const findMatch = () => {
            let result;

            // First: check for an exact match
            result = effectiveRates.find(search(serviceId, payerId, caregiverId));
            if (result) return result;

            // Find partial matches in order of caregiver ID, payer ID, then service ID
            result = effectiveRates.find(search(null, payerId, caregiverId));
            if (result) return result;
            result = effectiveRates.find(search(serviceId, null, caregiverId));
            if (result) return result;
            result = effectiveRates.find(search(null, null, caregiverId));
            if (result) return result;
            result = effectiveRates.find(search(serviceId, payerId, null));
            if (result) return result;
            result = effectiveRates.find(search(null, payerId, null));
            if (result) return result;
            result = effectiveRates.find(search(serviceId, null, null));
            if (result) return result;

            // Use fallback rates or 0
            result = effectiveRates.find(search(null, null, null));
            if (result) return result;

            return {};
        }

        const rateObj = findMatch();
        return this.getRatesByType(rateObj, fixedRates)
    }

    static getRatesByType(rateObj, fixedRates = false) {
        return {
            caregiver_rate: (fixedRates ? rateObj.caregiver_fixed_rate : rateObj.caregiver_hourly_rate) || 0,
            client_rate: (fixedRates ? rateObj.client_fixed_rate : rateObj.client_hourly_rate) || 0,
        }
    }

    static getRateSpecificity(rateObj) {
        let specificity = 0;
        specificity += rateObj.caregiver_id === null ? 0 : 1;
        specificity += rateObj.payer_id === null ? 0 : 1;
        specificity += rateObj.service_id === null ? 0 : 1;
        return specificity;
    }
}

export default RateFactory;