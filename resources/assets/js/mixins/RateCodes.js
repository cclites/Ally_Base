export default {
    data() {
        return {
            rateCodes: null,
        }
    },

    computed: {
        hourlyRateCodes() {
            if (!this.rateCodes) return [];
            return this.rateCodes.filter(code => !code.fixed);
        },
        fixedRateCodes() {
            if (!this.rateCodes) return [];
            return this.rateCodes.filter(code => !!code.fixed);
        },
        clientHourlyRateCodes() {
            return this.hourlyRateCodes.filter(code => code.type === 'client');
        },
        clientFixedRateCodes() {
            return this.fixedRateCodes.filter(code => code.type === 'client');
        },
        caregiverHourlyRateCodes() {
            return this.hourlyRateCodes.filter(code => code.type === 'caregiver');
        },
        caregiverFixedRateCodes() {
            return this.fixedRateCodes.filter(code => code.type === 'caregiver');
        },
    },

    methods: {
        async fetchRateCodes(type = '') {
            const response = await axios.get('/business/rate-codes?type=' + type);
            this.rateCodes = response.data;
        },
        updateRateCode(code) {
            let index = this.rateCodes.findIndex(item => item.id == code.id);
            if (index === -1) {
                this.rateCodes.push(code);
            }
            else {
                Vue.set(this.rateCodes, index, code);
            }
        },
        isUsingRateCodes(business) {
            return !!business.use_rate_codes;
        },
        hasClientRateStructure(business) {
            return business.rate_structure === 'client_rate';
        }
    }
}