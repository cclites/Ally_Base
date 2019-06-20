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
        },

        serviceHours()
        {
            return this.form.services.reduce((carry, service) => carry + parseFloat(service.duration), 0);
        },

        isUsingOvertime() {
            if (this.billingType == 'services') {
                let flag = false;
                this.form.services.forEach(item => {
                    if (['overtime', 'holiday'].includes(item.hours_type)) {
                        flag = true;
                    }
                })
                return flag;
            } else {
                return ['overtime', 'holiday'].includes(this.form.hours_type);
            }
        },

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
                const response = await axios.get(`/business/clients/${clientId}/rates`);
                this.clientRates = response.data;
                this.fetchAllRates();
            }
        },

        async loadClientPayers(clientId, resetPayers = false) {
            if (clientId) {
                const response = await axios.get(`/business/clients/${clientId}/payers/unique`);
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
            console.log('Adding service', service);
            const newService = {
                id: service.id || null,
                service_id: service.service_id ? service.service_id : (this.defaultService ? this.defaultService.id : null),
                payer_id: service.payer_id == 0 ? 0 : service.payer_id || null,
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
                },
                quickbooks_service_id: service.quickbooks_service_id || '',
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

        recalculateAllRates(form) {
            if (form.services.length) {
                for(let service of this.form.services) {
                    this.updateProviderRates(service);
                }
            } else {
                this.updateProviderRates(this.form);
            }
        },

        recalculateRates(rates, clientRate, caregiverRate) {
            if (clientRate === null || caregiverRate === null) return;
            rates.ally_fee = RateFactory.getAllyFee(this.allyPct, clientRate).toFixed(2);
            rates.provider_fee = RateFactory.getProviderFee(clientRate, caregiverRate, this.allyPct, true).toFixed(2);
        },

        updateProviderRates(item) {
            this.recalculateRates(item, item.client_rate, item.caregiver_rate);
        },

        updateClientRates(item) {
            item.client_rate = RateFactory.getClientRate(item.provider_fee, item.caregiver_rate, this.allyPct).toFixed(2);
            item.ally_fee = RateFactory.getAllyFee(this.allyPct, item.client_rate).toFixed(2);
            if (!item.caregiver_rate) {
                item.caregiver_rate = "0.00";
            }
        },

        fetchDefaultRate(service) {
            const ratesObj = RateFactory.findMatchingRate(this.clientRates, this.startDate, service.service_id, service.payer_id, this.form.caregiver_id, this.form.fixed_rates);
            service.default_rates.client_rate = ratesObj.client_rate;
            service.default_rates.caregiver_rate = ratesObj.caregiver_rate;
            this.recalculateRates(service.default_rates, service.default_rates.client_rate, service.default_rates.caregiver_rate);
            this.modifyRate(service.default_rates, this.getMultiplierType(service.hours_type), this.getMultiplier(service.hours_type));
        },

        fetchAllRates(form) {
            if (!form) form = this.form;

            this.fetchDefaultRate(form);
            for(let service of form.services) {
                this.fetchDefaultRate(service);
            }
        },

        handleChangedBillingType(form, type) {
            console.log('handleChangedBillingType', form);
            if (type === 'services') {
                this.form.service_id = null;
                this.form.fixed_rates = false;
                if (!this.form.services.length) {
                    console.log('added service from handleChangedBillingType');
                    this.addService({duration: this.scheduledHours || this.duration || 1 });
                }
            } else {
                this.form.service_id = this.defaultService.id;
                this.form.fixed_rates = type === 'fixed';
            }
            this.fetchAllRates();
        },

        handleChangedDefaultRates(form, value) {
            console.log('handleChangedDefaultRates', value, form);
            // initiated from watcher
            if (value) {
                this.fetchDefaultRate(form);
                form.client_rate = null;
                form.caregiver_rate = null;
                for(let service of form.services) {
                    this.fetchDefaultRate(service);
                    service.client_rate = null;
                    service.caregiver_rate = null;
                }
            } else {
                console.log('handleChangedDefaultRates: use defaults is off, do nothing');
                // if (! form.default_rates) {
                //     console.log('skipped');
                //     return;
                // }
                // console.log('Assigning all rates to the defaults.');
                // form.client_rate = form.default_rates.client_rate || null;
                // form.caregiver_rate = form.default_rates.caregiver_rate || null;
                // this.recalculateRates(form, form.client_rate, form.caregiver_rate);
                // for(let service of form.services) {
                //     service.client_rate = service.default_rates.client_rate || 0;
                //     service.caregiver_rate = service.default_rates.caregiver_rate || 0;
                //     this.recalculateRates(service, service.client_rate, service.caregiver_rate);
                // }
            }
        },

        handleChangedHoursType(rates, newVal, oldVal) {
            var OT = parseFloat(this.business.ot_multiplier);
            var HOL = parseFloat(this.business.hol_multiplier);
            switch(newVal) {
                case 'overtime':
                    if (oldVal == 'holiday') {
                        this.modifyRate(rates, this.business.hol_behavior, HOL, true);
                    }
                    this.modifyRate(rates, this.business.ot_behavior, OT);
                    break;
                case 'holiday':
                    if (oldVal == 'overtime') {
                        this.modifyRate(rates, this.business.ot_behavior, OT, true);
                    }
                    this.modifyRate(rates, this.business.hol_behavior, HOL);
                    break;
                case 'default':
                    if (oldVal == 'holiday') {
                        this.modifyRate(rates, this.business.hol_behavior, HOL, true);
                    } else if (oldVal == 'overtime') {
                        this.modifyRate(rates, this.business.ot_behavior, OT, true);
                    }
                    break;
            }
        },

        modifyRate(rates, multiplierType = null, multiplier = 1.0, reduce = false) {
            let cgRate = (parseFloat(rates.caregiver_rate) * multiplier).toFixed(2);
            let providerFee = (parseFloat(rates.provider_fee) * multiplier).toFixed(2);
            if (reduce) {
                cgRate = (parseFloat(rates.caregiver_rate) / multiplier).toFixed(2);
                providerFee = (parseFloat(rates.provider_fee) / multiplier).toFixed(2);
            }
            switch (multiplierType) {
                case 'caregiver':
                    rates.caregiver_rate = cgRate;
                    this.updateClientRates(rates)
                    break;
                case 'provider': 
                    rates.provider_fee = providerFee;
                    this.updateClientRates(rates);
                    break;
                case 'both':
                    rates.caregiver_rate = cgRate;
                    rates.provider_fee = providerFee;
                    this.updateClientRates(rates)
                    break;
                case null:
                default:
                    return;
            }
        },

        getMultiplier(hoursType) {
            switch(hoursType) {
                case 'overtime':
                    return parseFloat(this.business.ot_multiplier);
                case 'holiday':
                    return parseFloat(this.business.hol_multiplier);
                default:
                    return parseFloat(1.0);
            }
        },

        getMultiplierType(hoursType) {
            switch(hoursType) {
                case 'overtime':
                    return this.business.ot_behavior;
                case 'holiday':
                    return this.business.hol_behavior;
                default:
                    return null;
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
