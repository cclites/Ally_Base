export default {
    data() {
        return {
            'businessSettingsStore': null,
        }
    },

    mounted() {
        axios.get('/business/settings?json=1')
            .then(response => {
                this.businessSettingsStore = response.data;
            })
            .catch(err => {
                this.businessSettingsStore = {};
            });
    },

    methods: {
        businessSettings() {
            return this.businessSettingsStore;
        }
    },
}