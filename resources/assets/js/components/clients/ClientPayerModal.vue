<template>
    <form @submit.prevent="submit()" @keydown="form.clearError($event.target.name)">
        <b-modal :title="title"
            v-model="showModal"
            size="lg"
            @cancel="onCancel"
        >
            <b-row class="mb-2">
                <b-col lg="6">
                    <b-form-group label="Payer" label-for="payer_id" label-class="required">
                        <b-select v-model="form.payer_id">
                            <option value="">(Client)</option>
                            <option v-for="payer in payers" :value="payer.id" :key="payer.id">{{ payer.name }}</option>
                        </b-select>
                        <input-help :form="form" field="payer_id"></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Policy Number" label-for="policy_number">
                        <b-form-input v-model="form.policy_number" type="text" />
                        <input-help :form="form" field="policy_number"></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row class="mb-2">
                <b-col lg="6">
                    <b-form-group label="Effective Start" label-for="effective_start" label-class="required">
                        <mask-input v-model="form.effective_start"
                            name="effective_start"
                            type="date"
                            class="date-input"
                        ></mask-input>
                        <input-help :form="form" field="effective_start"></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Effective End" label-for="effective_end" label-class="required">
                        <mask-input v-model="form.effective_end"
                            name="effective_end"
                            type="date"
                            class="date-input"
                        ></mask-input>
                        <input-help :form="form" field="effective_end"></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row class="mb-2">
                <b-col lg="6">
                    <b-form-group label="Payment Allocation" label-for="payment_allocation" label-class="required">
                        <b-select v-model="form.payment_allocation">
                            <option value="balance">Balance</option>
                            <option value="split">Split</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </b-select>
                        <input-help :form="form" field="payment_allocation"></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group v-if="showPaymentAllowance" label="Payment Allowance" label-for="payment_allowance" label-class="required">
                        <b-form-input name="payment_allowance"
                            type="number"
                            step="any"
                            min="0"
                            max="9999999.99"
                            required
                            v-model="form.payment_allowance"
                        ></b-form-input>
                        <input-help :form="form" field="payment_allowance"></input-help>
                    </b-form-group>
                    <b-form-group v-if="showSplitPercentage" label="Split Percentage" label-for="split_percentage" label-class="required">
                        <b-form-input name="split_percentage"
                            type="number"
                            step="any"
                            min="0"
                            max="100.00"
                            required
                            v-model="form.split_percentage"
                        ></b-form-input>
                        <input-help :form="form" field="split_percentage"></input-help>
                    </b-form-group>
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
    export default {
        components: {},

        props: {
            value: Boolean,
            source: Object,
            payers: Array,
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
                return (this.source.id) ? 'Edit Client Payer' : 'Add Client Payer';
            },
            buttonText() {
                return (this.source.id) ? 'Save' : 'Add';
            },
            showPaymentAllowance() {
                return ['daily', 'weekly', 'monthly'].includes(this.form.payment_allocation);
            },
            showSplitPercentage() {
                return ['split'].includes(this.form.payment_allocation);
            },
        },

        methods: {
            makeForm(defaults = {}) {
                return new Form({
                    payer_id: defaults.payer_id ? defaults.payer_id : '',
                    policy_number: defaults.policy_number,
                    effective_start: moment(defaults.effective_start).format('MM/DD/YYYY'),
                    effective_end: moment(defaults.effective_end).format('MM/DD/YYYY'),
                    payment_allocation: defaults.payment_allocation,
                    payment_allowance: defaults.payment_allowance,
                    split_percentage: defaults.split_percentage,
                });
            },

            submit() {
                this.loading = true;
                let method = this.source.id ? 'patch' : 'post';
                let url = this.source.id ? `/business/clients/${this.source.client_id}/payers/${this.source.id}` : `/business/clients/${this.source.client_id}/payers`;
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
