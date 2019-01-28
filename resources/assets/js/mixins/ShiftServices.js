import RateFactory from "../classes/RateFactory";

export default {
    // Common data shared between shift and schedule services
    data() {
        return {
            billingTypes: ['hourly', 'fixed', 'services'],
            billingType: 'hourly',
            defaultRates: true,
            clientPayers: [],
            clientRates: [],
            services: [],
        }
    },

    computed: {

        defaultService() {
            return this.services.find(item => item.default === true) || {};
        }

    },

    mounted() {
        this.fetchServices();
    },

    methods: {

        async fetchServices() {
            let response = await axios.get('/business/services?json=1');
            if (Array.isArray(response.data)) {
                this.services = response.data;
            } else {
                this.services = [];
            }
        },

        async loadClientRates(clientId) {
            if (clientId) {
                const response = await axios.get(`/business/clients/${this.form.client_id}/rates`);
                this.clientRates = response.data;
                this.fetchAllRates();
            }
        },

        async loadClientPayers(clientId, resetPayers = false) {
            if (this.form.client_id) {
                const response = await axios.get(`/business/clients/${this.form.client_id}/payers/unique`);
                this.clientPayers = response.data;
                if (resetPayers) this.resetServicePayers();
            }
        },

        initServicesFromObject(objectThatContainsServices)
        {
            const obj = objectThatContainsServices;
            if (obj.services) {
                for(let service of obj.services) {
                    this.addService(service);
                    if (service.client_rate !== null) {
                        this.defaultRates = false;
                    }
                    this.billingType = 'services';
                }
            }
        },

        addService(service = {}) {
            const newService = {
                id: service.id || null,
                service_id: service.service_id ? service.service_id : (this.defaultService ? this.defaultService.id : null),
                payer_id: service.payer_id || null,
                hours_type: service.hours_type || 'default',
                duration: service.duration || 1,
                caregiver_rate: service.caregiver_rate || null,
                client_rate: service.client_rate || null,
                provider_fee: null,
                ally_fee: null,
                default_rates: {
                    'client_rate': null,
                    'caregiver_rate': null,
                    'provider_fee': null,
                    'ally_fee': null,
                }
            };
            if (!service.id) {
                this.fetchDefaultRate(newService);
            } else {
                this.recalculateRates(newService, newService.client_rate, newService.caregiver_rate);
            }
            this.form.services.push(newService);
        },

        removeService(index) {
            Vue.delete(this.form.services, index);
        },

        resetServicePayers() {
            if (this.form.payer_id) {
                let index = this.clientPayers.findIndex(payer => payer.id == service.payer_id);
                this.form.payer_id = (index >= 0) ? this.form.payer_id : null;
            }
            this.form.services = this.form.services.map(service => {
                let index = this.clientPayers.findIndex(payer => payer.id == service.payer_id);
                service.payer_id = (index >= 0) ? service.payer_id : null;
                return service;
            });
        },

        recalculateRates(rates, clientRate, caregiverRate) {
            rates.ally_fee = RateFactory.getAllyFee(this.allyPct, clientRate);
            rates.provider_fee = RateFactory.getProviderFee(clientRate, caregiverRate, this.allyPct, true);
        },

        fetchDefaultRate(service) {
            const ratesObj = RateFactory.findMatchingRate(this.clientRates, this.startDate, service.service_id, service.payer_id, this.form.caregiver_id, this.form.fixed_rates);
            service.default_rates.client_rate = ratesObj.client_rate;
            service.default_rates.caregiver_rate = ratesObj.caregiver_rate;
            this.recalculateRates(service.default_rates, service.default_rates.client_rate, service.default_rates.caregiver_rate);
        },

        fetchAllRates(form) {
            if (!form) form = this.form;

            this.fetchDefaultRate(form);
            for(let service of form.services) {
                this.fetchDefaultRate(service);
            }
        },

        handleChangedBillingType(form, type) {
            if (type === 'services') {
                this.form.service_id = null;
                this.form.fixed_rates = false;
                if (!this.form.services.length) {
                    console.log('added service from handleChangedBillingType');
                    this.addService();
                }
            } else {
                this.form.service_id = this.defaultService.id;
                this.form.fixed_rates = type === 'fixed';
            }
            this.fetchAllRates();
        },

        handleChangedDefaultRates(form, value) {
            // initiated from watcher
            if (value) {
                form.client_rate = null;
                form.caregiver_rate = null;
                for(let service of form.services) {
                    service.client_rate = null;
                    service.caregiver_rate = null;
                }
            } else {
                form.client_rate = form.default_rates.client_rate || 0;
                form.caregiver_rate = form.default_rates.caregiver_rate || 0;
                this.recalculateRates(form, form.client_rate, form.caregiver_rate);
                for(let service of form.services) {
                    service.client_rate = service.default_rates.client_rate || 0;
                    service.caregiver_rate = service.default_rates.caregiver_rate || 0;
                    this.recalculateRates(service, service.client_rate, service.caregiver_rate);
                }
            }
        },

    },

    watch: {

        defaultRates(val) {
            if (this.changedDefaultRates) this.changedDefaultRates(val);
            else this.handleChangedDefaultRates(this.form, val);
        },

        billingType(val) {
            if (this.changedBillingType) this.changedBillingType(val);
            else this.handleChangedBillingType(this.form, val);
        }

    }
}
