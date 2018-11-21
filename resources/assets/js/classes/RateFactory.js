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
}

export default RateFactory;