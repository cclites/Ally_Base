export default {
    data() {
        return {
            'businessSettingsStore': {},
        }
    },

    computed: {
        clientRateStructure() {
            return this.businessSettings().rate_structure === 'client_rate';
        },
        usingRateCodes() {
            return !!this.businessSettings().use_rate_codes;
        }
    },

    async mounted() {
        await axios.get('/business-settings?json=1')
            .then(response => {
                this.businessSettingsStore = response.data;
            })
            .catch(err => {
                this.businessSettingsStore = {};
                this.mounted(); // re-fetch
            });
    },

    methods: {
        businessSettings() {
            return this.businessSettingsStore;
        }
    },
}