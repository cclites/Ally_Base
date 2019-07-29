<template>
    <b-card
        header="Manual Charge Adjustment"
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
                    <b-form-group label="Client" label-for="client_id">
                        <b-form-select id="client_id"
                                       v-model="form.client_id"
                        >
                            <option value="">--Business Transaction--</option>
                            <option v-for="client in filteredClients" :value="client.id" :key="client.id">{{ client.nameLastFirst }} ({{ client.id }})</option>
                        </b-form-select>
                        <input-help :form="form" field="client_id" text="Select a client or run a business transaction" />
                    </b-form-group>
                    <b-form-group label="Transaction Type" label-for="type">
                        <b-form-select id="type"
                                       v-model="form.type"
                        >
                            <option value="payment">Charge</option>
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
                    <b-form-group label="Notes" label-for="notes">
                        <b-textarea id="notes"
                                    :rows="3"
                                    v-model="form.notes"
                        />
                        <input-help :form="form" field="notes" text="Enter a note explaining why this adjustment has occurred." />
                    </b-form-group>
                    <b-btn @click="showConfirmModal" :disabled="submitting">Submit</b-btn>
                    <span class="text-danger warning">DO NOT USE THIS FOR REFUNDS. USE THE CHARGES REPORT AND REFUND FROM THERE.</span>
                </form>
            </b-col>
        </b-row>

        <b-modal title="Confirm Manual Adjustment" v-model="confirmModal">
            <p><strong>Are You sure?</strong></p>
            <p>Business: {{ businessName }}</p>
            <p>Client: {{ clientName }}</p>
            <p>Type: {{ form.type }}</p>
            <p>Amount: {{ moneyFormat(form.amount)}}</p>

            <div slot="modal-footer">
                <b-btn variant="info" @click="submit()">Yes</b-btn>
                <b-btn variant="default" @click="confirmModal=false">No</b-btn>
            </div>

        </b-modal>

    </b-card>
</template>

<script>

    import FormatsNumbers from '../../mixins/FormatsNumbers';

    export default {
        props: {},
        mixins: [FormatsNumbers],

        data() {
            return {
                'businesses': [],
                'clients': [],
                'form': new Form(),
                'submitting': false,
                'confirmModal': false,
            }
        },

        computed: {
            filteredClients() {
                if (this.form.business_id) {
                    return this.clients.filter(obj => obj.business_id == this.form.business_id);
                }
                return this.clients;
            },
            clientName(){
                if (this.form.client_id) {
                    let client = this.clients.find(obj => obj.id == this.form.client_id);
                    return  client.lastname + ", " + client.firstname;
                }
                return '';
            },
            businessName(){
                if (this.form.business_id) {
                    let business = this.businesses.find(obj => obj.id == this.form.business_id);
                    return business.name;
                }
                return '';

            }
        },

        mounted() {
            this.makeForm();
            this.loadBusinesses();
            this.loadClients();
        },

        methods: {
            makeForm() {
                this.form = new Form({
                    'business_id': "",
                    'client_id': "",
                    'amount': 0,
                    'type': 'payment',
                    'adjustment': true,
                    'notes': "",
                });
            },

            submit() {

                if (this.submitting) return;
                this.submitting = true;

                this.form.post('/admin/charges/manual')
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

            loadClients() {
                axios.get('/admin/clients?json=1').then(response => this.clients = response.data);
            },

            showConfirmModal(){
                this.confirmModal = true;
            }

        },

        watch: {
            business_id(newVal, oldVal) {
                if (this.clients.find(obj => obj.id == this.form.client_id && obj.business_id == newVal)) {
                    return;
                }
                this.form.client_id = '';
            },
        },


    }
</script>

<style>
  span.warning{
      padding-left: 12px;
      font-size: 15px;
      font-weight: 600;
  }
</style>
