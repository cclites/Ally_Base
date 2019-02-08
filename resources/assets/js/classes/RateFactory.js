import moment from "moment";

class RateFactory {

    static getChargedRate(caregiverRate, providerFee = 0.0, clientRate = 0.0, clientRateStructure = false)
    {
        return clientRateStructure ?  parseFloat(clientRate) : parseFloat(providerFee) + parseFloat(caregiverRate);
    }

    static getAllyFee(percentage, chargedRate)
    {
        let allyFee = parseFloat(percentage) * parseFloat(chargedRate);
        return parseFloat(allyFee.toFixed(2));
    }

    static getProviderFee(clientRate, caregiverRate, allyPct, allyFeeIncluded = false)
    {
        let chargedRate = parseFloat(clientRate);
        let allyFee = this.getAllyFee(allyPct, chargedRate);

        return chargedRate - parseFloat(caregiverRate) - (allyFeeIncluded ? allyFee : 0);
    }

    static getClientRate(providerFee, caregiverRate, allyPct)
    {
        return this.getChargedRate(caregiverRate, providerFee);
    }

    static findMatchingRate(ratesArray, effectiveDate, serviceId, payerId, caregiverId, fixedRates = false)
    {
        effectiveDate = moment(effectiveDate).format('YYYY-MM-DD');
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