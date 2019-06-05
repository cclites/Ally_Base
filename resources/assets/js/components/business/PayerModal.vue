<template>
    <form @submit.prevent="submitForm()" @keydown="form.clearError($event.target.name)">
        <b-modal :title="title"
            v-model="showModal"
            size="lg"
            class="modal-fit-more"
            @cancel="onCancel"
        >
            <b-row class="">
                <b-col lg="6">
                    <b-form-group label="Payer Name" label-for="name" label-class="required">
                        <b-form-input v-model="form.name" type="text" required />
                        <input-help :form="form" field="name"></input-help>
                    </b-form-group>
                    <b-form-group label="NPI Number" label-for="npi_number">
                        <b-form-input v-model="form.npi_number" type="text" />
                        <input-help :form="form" field="npi_number"></input-help>
                    </b-form-group>
                    <b-form-group label="Start of Service Week" label-for="week_start" label-class="required">
                        <b-select v-model="form.week_start">
                            <option value="0">Sunday</option>
                            <option value="1">Monday</option>
                            <option value="2">Tuesday</option>
                            <option value="3">Wednesday</option>
                            <option value="4">Thursday</option>
                            <option value="5">Friday</option>
                            <option value="6">Saturday</option>
                        </b-select>
                        <input-help :form="form" field="week_start"></input-help>
                    </b-form-group>
                    <b-form-group label="Payment Method" label-for="payment_method_type" label-class="required">
                        <b-select v-model="form.payment_method_type">
                            <option value="businesses">Provider Pay (Ally will pay caregivers)</option>
                            <option :value="null">Offline (Caregivers will NOT be paid)</option>
                        </b-select>
                        <input-help :form="form" field="payment_method_type"></input-help>
                    </b-form-group>
                    <b-form-group label="MCO / Payer Identifier" label-for="payer_code">
                        <b-form-input type="text" v-model="form.payer_code" />
                        <input-help :form="form" field="payer_code" text=""></input-help>
                    </b-form-group>
                    <b-form-group label="Plan Identifier" label-for="plan_code">
                        <b-form-input type="text" v-model="form.plan_code" />
                        <input-help :form="form" field="plan_code" text=""></input-help>
                    </b-form-group>
                    <b-form-group label="Invoice Format" label-for="invoice_format">
                        <b-form-input
                            v-model="form.invoice_format"
                            id="invoice_format"
                            name="invoice_format"
                            type="text"
                        ></b-form-input>
                        <input-help :form="form" field="invoice_format" text=""></input-help>
                    </b-form-group>
                    <b-form-group label="Transmission Method" label-for="transmission_method">
                        <b-select v-model="form.transmission_method">
                            <option value="">-- Select Transmission Method --</option>
                            <option value="HHA">HHAeXchange</option>
                            <option value="TELLUS">Tellus</option>
                            <option value="MANUAL">Mail/Email/Fax</option>
                        </b-select>
                        <input-help :form="form" field="transmission_method"></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Email" label-for="email">
                        <b-form-input type="email" v-model="form.email" />
                        <input-help :form="form" field="email" text=""></input-help>
                    </b-form-group>
                    <b-form-group label="Address Line 1" label-for="address1">
                        <b-form-input v-model="form.address1" type="text" />
                        <input-help :form="form" field="address1" text=""></input-help>
                    </b-form-group>
                    <b-form-group label="Address Line 2" label-for="address2">
                        <b-form-input type="text" v-model="form.address2" />
                        <input-help :form="form" field="address2" text=""></input-help>
                    </b-form-group>
                    <b-form-group label="City" label-for="city">
                        <b-form-input type="text" v-model="form.city" />
                        <input-help :form="form" field="city" text=""></input-help>
                    </b-form-group>
                    <b-form-group label="State" label-for="state">
                        <b-form-input type="text" v-model="form.state" />
                        <input-help :form="form" field="state" text=""></input-help>
                    </b-form-group>
                    <b-form-group label="Zip Code" label-for="zip">
                        <b-form-input type="text" v-model="form.zip" />
                        <input-help :form="form" field="zip" text=""></input-help>
                    </b-form-group>
                    <b-form-group label="Phone Number" label-for="phone_number">
                        <b-form-input v-model="form.phone_number" type="text" />
                        <input-help :form="form" field="phone_number" text=""></input-help>
                    </b-form-group>
                    <b-form-group label="Fax Number" label-for="fax_number">
                        <b-form-input type="text" v-model="form.fax_number" />
                        <input-help :form="form" field="fax_number" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Transmission Method" label-for="transmission_method" label-class="required">
                        <b-select v-model="form.transmission_method">
                            <option value="">-- Select Transmission Method --</option>
                            <option value="-" disabled>Direct Transmission:</option>
                            <option :value="CLAIM_SERVICE.HHA">HHAeXchange</option>
                            <option :value="CLAIM_SERVICE.TELLUS">Tellus</option>
                            <option :value="CLAIM_SERVICE.CLEARINGHOUSE">CareExchange LTC Clearinghouse</option>
                            <option value="-" disabled>-</option>
                            <option value="-" disabled>Offline:</option>
                            <option :value="CLAIM_SERVICE.EMAIL">Email</option>
                            <option :value="CLAIM_SERVICE.FAX">Fax</option>
                        </b-select>
                        <input-help :form="form" field="transmission_method"></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="MCO / Payer Identifier" label-for="payer_code">
                        <b-form-input type="text" v-model="form.payer_code" />
                        <input-help :form="form" field="payer_code" text=""></input-help>
                    </b-form-group>
                    <b-form-group label="Plan Identifier" label-for="plan_code">
                        <b-form-input type="text" v-model="form.plan_code" />
                        <input-help :form="form" field="plan_code" text=""></input-help>
                    </b-form-group>
                    <b-form-group label="Contact Name" label-for="contact_name">
                        <b-form-input
                            v-model="form.contact_name"
                            id="contact_name"
                            name="contact_name"
                            type="text"
                        ></b-form-input>
                        <input-help :form="form" field="contact_name" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <business-payer-rates-table 
                        ref="ratesTable"
                        :rates="form.rates" 
                        :services="services"
                    ></business-payer-rates-table>
                </b-col>
            </b-row>
            <div slot="modal-footer">
                <b-button variant="success"
                        type="submit"
                        :disabled="loading"
                >
                    {{ buttonText }}
                </b-button>
                <b-btn variant="default" @click="showModal=false">Cancel</b-btn>
            </div>
        </b-modal>
    </form>
</template>

<script>
    import Constants from '../../mixins/Constants';

    export default {
        mixins: [Constants],

        components: {},

        props: {
            value: Boolean,
            source: Object,
            services: Array,
        },

        data() {
            return {
                form: this.makeForm(this.source),
                loading: false,
                showModal: this.value,
            }
        },

        computed: {
            title() {
                return (this.source.id) ? 'Edit Payer' : 'Add New Payer';
            },
            buttonText() {
                return (this.source.id) ? 'Save' : 'Create';
            },
        },

        methods: {
            makeForm(defaults = {}) {
                console.log(defaults);
                return new Form({
                    name: defaults.name,
                    npi_number: defaults.npi_number,
                    week_start: defaults.week_start,
                    rates: defaults.rates,
                    address1: defaults.address1,
                    address2: defaults.address2,
                    city: defaults.city,
                    state: defaults.state,
                    zip: defaults.zip,
                    contact_name: defaults.contact_name,
                    invoice_format: defaults.invoice_format,
                    payment_method_type: defaults.payment_method_type === undefined ? "businesses" : defaults.payment_method_type,
                    phone_number: defaults.phone_number,
                    fax_number: defaults.fax_number,
                    email: defaults.email,
                    transmission_method: defaults.transmission_method || '',
                    payer_code: defaults.payer_code || '',
                    plan_code: defaults.plan_code || '',
                });
            },

            submitForm() {
                this.loading = true;
                this.form.rates = this.$refs.ratesTable.items;
                let method = this.source.id ? 'patch' : 'post';
                let url = this.source.id ? `/business/payers/${this.source.id}` : '/business/payers';
                this.form.submit(method, url)
                    .then(response => {
                        this.$emit('saved', response.data.data);
                        this.showModal = false;
                    })
                    .finally(() => this.loading = false)
            },

            onCancel() {
                this.value = {};
            },
        },

        watch: {
            value(val) {
                if (! val) {
                    // clear the form on close so the data updates if the
                    // edit modal is opened again for the same object.
                    this.form = this.makeForm({});
                } else {
                    this.form = this.makeForm(this.source);
                }
                this.showModal = val;
            },
            showModal(val) {
                this.$emit('input', val);
            }
        }
    }
</script>

<style scoped>
    .loader {
        border: 8px solid #f3f3f3;
        border-radius: 50%;
        border-top: 8px solid #3498db;
        width: 50px;
        height: 50px;
        -webkit-animation: spin-data-v-7012acc5 2s linear infinite;
        animation: spin-data-v-7012acc5 2s linear infinite;
        margin: 0 auto;
    }

    /* Safari */
    @-webkit-keyframes spin {
        0% { -webkit-transform: rotate(0deg); }
        100% { -webkit-transform: rotate(360deg); }
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .error-msg {
        margin-top: 7px;
        color: red;
    }
</style>