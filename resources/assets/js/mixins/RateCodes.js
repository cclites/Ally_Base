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
        }
    },

    methods: {
        async fetchRateCodes(type = null) {
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
        }
    }
}