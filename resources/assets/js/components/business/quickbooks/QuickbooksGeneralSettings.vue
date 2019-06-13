<template>
    <div>
        <loading-card v-if="loading" text="Loading..." />

        <div v-else class="mb-2">
            <b-row>
                <b-col lg="6">
                    <h3>General Settings</h3>
                </b-col>
            </b-row>
            <b-row class="mb-4">
                <b-col lg="6">
                    <b-form-group label="Customer Name Format" label-for="name_format">
                        <b-select name="name_format" id="name_format" v-model="form.name_format" :disabled="busy">
                            <option value="">-- Select Name Format --</option>
                            <option value="first_last">John Doe</option>
                            <option value="last_first">Doe, John</option>
                        </b-select>
                        <input-help :form="form" field="name_format" text="Select how we should format client names when we create customers."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row class="mb-2" align-h="end">
                <b-col lg="6">
                    <h3>Service Mapping</h3>
                </b-col>
                <b-col md="6" class="text-right">
                    <b-btn variant="success" @click="refreshServices()" :disabled="busy">Sync Quickbooks Services</b-btn>
                </b-col>
            </b-row>
            <b-row>
                <b-col md="6">
                    <b-form-group label="Shift Service" label-for="shift_service_id">
                        <b-select name="shift_service_id" id="shift_service_id" v-model="form.shift_service_id" :disabled="busy">
                            <option value="">-- Pull From Shifts --</option>
                            <option v-for="item in quickbooksServices" :key="item.id" :value="item.id">{{ item.name }}</option>
                        </b-select>
                        <input-help :form="form" field="shift_service_id" text="Select the service to use for general shift entries."></input-help>
                    </b-form-group>

                    <b-form-group label="Mileage Service" label-for="mileage_service_id">
                        <b-select name="mileage_service_id" id="mileage_service_id" v-model="form.mileage_service_id" :disabled="busy">
                            <option value="">-- Map Mileage Service --</option>
                            <option v-for="item in quickbooksServices" :key="item.id" :value="item.id">{{ item.name }}</option>
                        </b-select>
                        <input-help :form="form" field="mileage_service_id" text="Select the service to use for shift mileage reimbursement"></input-help>
                    </b-form-group>

                    <b-form-group label="Expense Service" label-for="expense_service_id">
                        <b-select name="expense_service_id" id="expense_service_id" v-model="form.expense_service_id" :disabled="busy">
                            <option value="">-- Map Expense Service --</option>
                            <option v-for="item in quickbooksServices" :key="item.id" :value="item.id">{{ item.name }}</option>
                        </b-select>
                        <input-help :form="form" field="expense_service_id" text="Select the service to use for shift expenses."></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Refund Service" label-for="refund_service_id">
                        <b-select name="refund_service_id" id="refund_service_id" v-model="form.refund_service_id" :disabled="busy">
                            <option value="">-- Map Refund Service --</option>
                            <option v-for="item in quickbooksServices" :key="item.id" :value="item.id">{{ item.name }}</option>
                        </b-select>
                        <input-help :form="form" field="refund_service_id" text="Select the service to use for refunds."></input-help>
                    </b-form-group>

                    <b-form-group label="Adjustment Service" label-for="adjustment_service_id">
                        <b-select name="adjustment_service_id" id="adjustment_service_id" v-model="form.adjustment_service_id" :disabled="busy">
                            <option value="">-- Map Adjustment Service --</option>
                            <option v-for="item in quickbooksServices" :key="item.id" :value="item.id">{{ item.name }}</option>
                        </b-select>
                        <input-help :form="form" field="adjustment_service_id" text="Select the service to use for manual adjustments."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col md="6">
                    <b-btn variant="info" @click="save()" :disabled="busy">Save Changes</b-btn>
                </b-col>
            </b-row>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            connection: {
                type: [Array, Object],
                default: () => { return {}; },
            },
            businessId: {
                type: [String, Number],
                default: '',
            },
        },

        data() {
            return {
                form: new Form({
                    name_format: this.connection.name_format || '',
                    shift_service_id: this.connection.shift_service_id || '',
                    mileage_service_id: this.connection.mileage_service_id || '',
                    adjustment_service_id: this.connection.adjustment_service_id || '',
                    refund_service_id: this.connection.refund_service_id || '',
                    expense_service_id: this.connection.expense_service_id || '',
                }),
                loading: false,
                busy: false,
                quickbooksServices: [],
            }
        },

        mounted() {
            this.loading = true;
            this.fetchServices();
        },

        methods: {
            save() {
                if (! this.businessId) {
                    return;
                }

                this.busy = true;
                this.form.patch(`/business/quickbooks/${this.businessId}/settings`)
                    .then( ({ data }) => {
                    })
                    .catch(() => {})
                    .finally(() => {
                        this.busy = false;
                    });
            },

            fetchServices() {
                if (! this.businessId) {
                    return;
                }
                this.loading = true;

                axios.get(`/business/quickbooks/${this.businessId}/services`)
                    .then( ({ data }) => {
                        this.quickbooksServices = data;
                    })
                    .catch(() => {})
                    .finally(() => {
                        this.loading = false;
                    });
            },

            refreshServices() {
                    if (! this.businessId) {
                        return;
                    }
                    this.busy = true;

                    let form = new Form({});
                    form.post(`/business/quickbooks/${this.businessId}/services/sync`)
                        .then( ({ data }) => {
                            this.quickbooksServices = data.data;
                        })
                        .catch(() => {})
                        .finally(() => {
                            this.busy = false;
                        });
            }
        },

        watch: {
            businessId(newValue, oldValue) {
                this.fetchServices();
            }
        },
    }
</script>
