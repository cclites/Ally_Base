<template>
    <b-card
        header="Manual Deposit Adjustment"
        header-text-variant="white"
        header-bg-variant="info"
        >
        <b-row>
            <b-col lg="6">
                <form @submit.prevent="submit()" @keydown="form.clearError($event.target.name)">
                    <b-form-group label="Business" label-for="business_id">
                        <b-form-select id="business_id"
                                       v-model="form.business_id"
                        >
                            <option value="">--Select Business--</option>
                            <option v-for="business in businesses" :value="business.id" :key="business.id">{{ business.name }}</option>
                        </b-form-select>
                        <input-help :form="form" field="business_id" text="Select a business" />
                    </b-form-group>
                    <b-form-group label="Caregiver" label-for="caregiver_id">
                        <b-form-select id="caregiver_id"
                                       v-model="form.caregiver_id"
                        >
                            <option value="">--Business Transaction--</option>
                            <option v-for="caregiver in caregivers" :value="caregiver.id" :key="caregiver.id">{{ caregiver.nameLastFirst }} ({{ caregiver.id }})</option>
                        </b-form-select>
                        <input-help :form="form" field="caregiver_id" text="Select a caregiver or run a business transaction" />
                    </b-form-group>
                    <b-form-group label="Transaction Type" label-for="type">
                        <b-form-select id="type"
                                       v-model="form.type"
                        >
                            <option value="deposit">Send Deposit</option>
                            <option value="withdrawal">Withdrawal/Charge</option>
                        </b-form-select>
                        <input-help :form="form" field="type" text="Select the type of transaction for the adjustment." />
                    </b-form-group>
                    <b-form-group label="Amount" label-for="amount">
                        <b-form-input type="number"
                                      id="amount"
                                      v-model="form.amount"
                                      step="any"
                        />
                        <input-help :form="form" field="amount" text="Enter the transaction amount" />
                    </b-form-group>
                    <div class="form-check">
                        <input-help :form="form" field="adjustment" text="Record an adjustment entry (this should normally be checked)" />
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="adjustment" v-model="form.adjustment" value="1">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description">Adjustment</span>
                        </label>
                    </div>
                    <b-form-group label="" label-for="amount">
                        <b-form-radio-group v-model="form.process">
                            <b-radio value="1">Process Immediately</b-radio>
                            <b-radio value="0">Create Invoice to Aggregate</b-radio>
                        </b-form-radio-group>
                        <input-help :form="form" field="process" text="" />
                    </b-form-group>
                    <b-form-group label="Notes" label-for="notes">
                        <b-textarea id="notes"
                                    :rows="3"
                                    v-model="form.notes"
                        />
                        <input-help :form="form" field="notes" text="Enter a note explaining why this adjustment has occurred." />
                    </b-form-group>
                    <b-btn type="submit" :disabled="submitting">Submit</b-btn>
                </form>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    export default {
        props: {},

        data() {
            return {
                'businesses': [],
                'caregivers': [],
                'form': new Form(),
                'submitting': false,
            }
        },

        computed: {

        },

        mounted() {
            this.makeForm();
            this.loadBusinesses();
            this.loadCaregivers();
        },

        methods: {

            makeForm() {
                this.form = new Form({
                    'business_id': "",
                    'caregiver_id': "",
                    'type': "",
                    'amount': 0,
                    'adjustment': true,
                    'notes': "",
                    process: 1,
                });
            },

            submit() {
                // Prevent duplicate submissions
                if (this.submitting) return;
                this.submitting = true;

                this.form.post('/admin/deposits/adjustment')
                    .then(response => {
                        this.makeForm();
                        this.submitting = false;
                    })
                    .catch(error => {
                        this.submitting = false;
                    });
            },

            loadBusinesses() {
                axios.get('/admin/businesses?json=1').then(response => this.businesses = response.data);
            },

            loadCaregivers() {
                axios.get('/admin/caregivers?json=1').then(response => this.caregivers = response.data);
            },
        },
    }
</script>
