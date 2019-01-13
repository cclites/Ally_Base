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

    static findMatchingRate(ratesArray, serviceId, payerId, caregiverId, fixedRates = false)
    {
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
            result = ratesArray.find(search(serviceId, payerId, caregiverId));
            if (result) return result;

            // Find partial matches in order of caregiver ID, payer ID, then service ID
            result = ratesArray.find(search(null, payerId, caregiverId));
            if (result) return result;
            result = ratesArray.find(search(serviceId, null, caregiverId));
            if (result) return result;
            result = ratesArray.find(search(null, null, caregiverId));
            if (result) return result;
            result = ratesArray.find(search(serviceId, payerId, null));
            if (result) return result;
            result = ratesArray.find(search(serviceId, null, null));
            if (result) return result;

            // Use fallback rates or 0
            result = ratesArray.find(search(null, null, null));
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
}

export default RateFactory;