<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="6">
                <b-form-select
                        v-model="businessId"
                        required
                >
                    <option value="">--Filter by Provider--</option>
                    <option v-for="business in businesses" :value="business.id" :key="business.id">{{ business.name }}</option>
                </b-form-select>
            </b-col>
            <b-col lg="6" class="text-right">
                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
        </b-row>
        <b-row class="mb-2">
            <b-col class="text-right">
                <b-button variant="info" @click=" loadData() ">Generate Report</b-button>
            </b-col>
        </b-row>

        <loading-card v-show="loading"></loading-card>

        <div v-show="! loading" class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :filter="filter"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
            >
                <template slot="notes" scope="row">
                    <span v-if="row.item.notes">
                        {{ row.item.notes.substr(0, 40) }} <a href="javascript:void(0)" @click="selectedHold = row.item">[+]</a>
                    </span>
                </template>
                <template slot="actions" scope="row">
                    <b-btn size="sm" @click="removeHold(row.item)" variant="primary">Remove Hold</b-btn>
                    <b-btn size="sm" :href="'/admin/transactions/' + row.item.last_transaction_id" v-if="row.item.last_transaction_id">View Last Transaction</b-btn>
                    <b-btn size="sm" @click="selectedHold = row.item">Edit Notes</b-btn>
                </template>
            </b-table>
        </div>

        <b-modal title="Edit Hold Details" v-model="showModal" size="lg">
            <b-container fluid>
                    <b-form-group label="Check Back On" label-for="check_back_on" label-class="required">
                        <date-picker v-model="form.check_back_on" required></date-picker>
                        <input-help :form="form" field="check_back_on" text=""></input-help>
                    </b-form-group>

                    <b-form-group label="Notes" label-for="notes">
                        <b-form-textarea :rows="4" v-model="form.notes"></b-form-textarea>
                        <input-help :form="form" field="notes" text=""></input-help>
                    </b-form-group>
            </b-container>

            <div slot="modal-footer">
                <b-button variant="info" @click="updateSelectedHold()">Save</b-button>
                <b-btn variant="default" @click="selectedHold = null">Cancel</b-btn>
            </div>
        </b-modal>
    </b-card>
</template>

<script>
    import FormatsNumbers from "../../mixins/FormatsNumbers";
    import FormatsDates from "../../mixins/FormatsDates";

    export default {
        mixins: [FormatsNumbers, FormatsDates],

        props: {},

        data() {
            return {
                sortBy: null,
                sortDesc: false,
                filter: null,
                items: [],
                loading: false,
                fields: [
                    {
                        key: 'name',
                        sortable: true,
                    },
                    {
                        key: 'type',
                        sortable: true,
                        formatter: x => _.startCase(x),
                    },
                    {
                        key: 'display_id',
                        sortable: true,
                    },
                    {
                        key: 'business',
                        label: 'Registry',
                        sortable: true,
                    },
                    // {
                    //     key: 'payment_outstanding',
                    //     label: 'Charges Outstanding',
                    //     sortable: true,
                    //     formatter: this.numberFormat
                    // },
                    // {
                    //     key: 'deposit_outstanding',
                    //     label: 'Deposits Outstanding',
                    //     sortable: true,
                    //     formatter: this.numberFormat
                    // },
                    {
                        key: 'notes',
                        sortable: true,
                    },
                    {
                        key: 'unpaid_invoices',
                        sortable: true,
                        // formatter: this.numberFormat
                    },
                    {
                        key: 'created_at',
                        label: 'Hold Date',
                        sortable: true,
                        formatter: x => this.formatDateTimeFromUTC(x),
                    },
                    {
                        key: 'check_back_on',
                        sortable: true,
                        formatter: x => this.formatDate(x),
                    },
                    'actions'
                ],
                businesses: [],
                businessId: "",
                selectedHold: null,
                form: new Form({}),
            }
        },

        computed: {
            showModal: {
                get() {
                    return !!this.selectedHold;
                },
                set(val) {
                    if (!val) this.selectedHold = null;
                }
            },
        },

        mounted() {
            this.loadBusinesses();
        },

        methods: {

            loadData() {
                this.loading = true;
                axios.get('/admin/reports/on_hold?json=1&business_id=' + this.businessId)
                    .then(response => {
                        this.items = response.data.map(item => {
                            item.display_id = item.user_id || item.business_id || null;
                            item.check_back_on = item.check_back_on || item.created_at.split(' ')[0];
                            return item;
                        });
                        this.loading = false;
                    })
                    .catch(e => {
                        this.loading = false;
                    });
            },

            loadBusinesses() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
            },

            removeHold(item) {
                let form = new Form();
                let url = '/admin/users/' + item.user_id + '/hold';
                if (item.type === 'business') {
                    url = '/admin/businesses/' + item.business_id + '/hold'
                }
                form.submit('delete', url)
                    .then(response => {
                        this.items = this.items.filter(current => current.id !== item.id);
                    });
            },

            async updateSelectedHold() {
                await this.form.patch(`/admin/payment-holds/${this.selectedHold.id}`);
                this.selectedHold.notes = this.form.notes;
                this.selectedHold.check_back_on = this.form.check_back_on;
                this.selectedHold = null;
            }
        },

        watch: {
            businessId() {
                this.loadData();
            },
            selectedHold(val) {
                this.form = new Form({
                    notes: val.notes || "",
                    check_back_on: val.check_back_on ? this.formatDate(val.check_back_on) : "",
                });
            }
        }
    }
</script>
