<template>
    <b-card
        header="Client Payers"
        header-text-variant="white"
        header-bg-variant="info"
        class="pb-3"
        >

        <div class="ml-auto mb-3">
            <b-btn variant="info" @click="add()">Add Client Payer</b-btn>
        </div>

        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     ref="table"
                     class="table-fit-more"
            >
                <template slot="priority" scope="data">
                    <b-btn size="sm" @click="shiftPriority(data.index)">
                        <i class="fa fa-arrow-up"></i>
                    </b-btn>
                </template>
                <template slot="payer_id" scope="row">
                    <b-select v-model="row.item.payer_id" class="form-control-sm">
                        <option :value="0">({{ client.name }})</option>
                        <option v-for="payer in payerOptions" :value="payer.id" :key="payer.id">{{ payer.name }}</option>
                        <option :value="1">OFFLINE</option>
                    </b-select>
                </template>
                <template slot="policy_number" scope="row">
                    <b-form-input v-model="row.item.policy_number" type="text" class="date-input form-control-sm" :disabled="row.item.payer_id == 0" />
                </template>
                <template slot="effective_start" scope="row">
                    <mask-input v-model="row.item.effective_start" type="date" class="date-input form-control-sm"></mask-input>
                </template>
                <template slot="effective_end" scope="row">
                    <mask-input v-model="row.item.effective_end" type="date" class="date-input form-control-sm"></mask-input>
                </template>
                <template slot="program_number" scope="row">
                    <b-form-input v-model="row.item.program_number" type="text" class="date-input form-control-sm" />
                </template>
                <template slot="cirts_number" scope="row">
                    <b-form-input v-model="row.item.cirts_number" type="text" class="date-input form-control-sm" />
                </template>
                <template slot="notes" scope="row">
                    <b-form-input v-model="row.item.notes" type="text" class="date-input form-control-sm" />
                </template>
                <template slot="payment_allocation" scope="row">
                    <b-select v-model="row.item.payment_allocation" class="form-control-sm" >
                        <option value="balance">Balance</option>
                        <option value="split">Split</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="manual">Manual</option>
                    </b-select>
                </template>
                <template slot="payment_allowance" scope="row">
                    <span v-if="['daily', 'weekly', 'monthly'].includes(row.item.payment_allocation)">
                        <b-form-input name="payment_allowance"
                            class="money-input form-control-sm"
                            type="number"
                            step="any"
                            min="0"
                            max="9999999.99"
                            required
                            v-model="row.item.payment_allowance"
                        ></b-form-input>
                    </span>
                    <span v-else>N/A</span>
                </template>
                <template slot="split_percentage" scope="row">
                    <span v-if="row.item.payment_allocation == 'split'">
                        <b-form-input name="split_percentage"
                            class="money-input form-control-sm"
                            type="number"
                            step="any"
                            min="0"
                            max="100.00"
                            required
                            v-model="row.item.split_percentage"
                        ></b-form-input>
                    </span>
                    <span v-else>N/A</span>
                </template>
                <template slot="actions" scope="data">
                    <b-btn size="sm" @click="remove(data.index)">
                        <i class="fa fa-trash"></i>
                    </b-btn>
                </template>
            </b-table>
        </div>
        <b-btn @click="save()" variant="success">Save Client Payers</b-btn>
    </b-card>
</template>

<script>
    import FormatsDates from "../../../mixins/FormatsDates";
    import FormatsStrings from "../../../mixins/FormatsStrings";

    export default {
        props: {
            'client': {},
            'payers': Array,
            'payerOptions': Array,
        },

        mixins: [ FormatsDates, FormatsStrings ],

        data() {
            return {
                items: [],
                totalRows: 0,
                perPage: 30,
                currentPage: 1,
                sortBy: 'priority',
                sortDesc: false,
                fields: [
                    {
                        key: 'priority',
                        label: 'Priority',
                    },
                    {
                        key: 'payer_id',
                        label: 'Payer',
                    },
                    {
                        key: 'policy_number',
                        label: 'Policy Number',
                    },
                    {
                        key: 'effective_start',
                        label: 'Effective Start',
                    },
                    {
                        key: 'effective_end',
                        label: 'Effective End',
                    },
                    {
                        key: 'program_number',
                        label: 'Program ID',
                    },
                    {
                        key: 'cirts_number',
                        label: 'CIRTS ID',
                    },
                    {
                        key: 'notes',
                        label: 'Print on Client Invoice',
                    },
                    {
                        key: 'payment_allocation',
                        label: 'Payment Allocation',
                    },
                    {
                        key: 'payment_allowance',
                        label: 'Payment Allowance',
                    },
                    {
                        key: 'split_percentage',
                        label: 'Split %',
                    },
                    {
                        key: 'actions',
                        label: '',
                        class: 'hidden-print'
                    },
                ],
            }
        },

        computed: {
        },

        methods: {
            array_move(arr, old_index, new_index) {
                if (new_index >= arr.length) {
                    var k = new_index - arr.length + 1;
                    while (k--) {
                        arr.push(undefined);
                    }
                }
                arr.splice(new_index, 0, arr.splice(old_index, 1)[0]);
                return arr; // for testing
            },

            shiftPriority(index) {
                if (index <= 0) {
                    return;
                }

                this.items.splice(index-1, 0, this.items.splice(index, 1)[0]);
                this.resetPriorities();
            },

            add() {
                this.items.push({
                    id: null,
                    priority: 0,
                    payer_id: 0,
                    policy_number: '',
                    effective_start: moment().format('MM/DD/YYYY'),
                    effective_end: moment('9999-12-31').format('MM/DD/YYYY'),
                    payment_allocation: 'balance',
                    payment_allowance: '0.00',
                    split_percentage: '0',
                    client_id: this.client.id,
                    notes: '',
                    cirts_number: '',
                    program_number: '',
                    // payer: {},
                });
                this.resetPriorities();
            },

            resetPriorities() {
                for (let i = 0; i < this.items.length; i++) {
                    this.items[i].priority = i + 1;
                }
            },

            remove(index) {
                if (confirm('Are you sure you wish to remove this payer line?  You\'ll still need to save your changes afterwards.')) {
                    if (index >= 0) {
                        this.items.splice(index, 1);
                    }
                    this.resetPriorities();
                }

            },

            castItem(data) {
                let item = JSON.parse(JSON.stringify(data));
                item.payer_id = item.payer_id ? item.payer_id : 0;
                item.effective_start = moment(item.effective_start, 'YYYY-MM-DD').format('MM/DD/YYYY');
                item.effective_end = moment(item.effective_end, 'YYYY-MM-DD').format('MM/DD/YYYY');
                item.split_percentage = item.split_percentage ? (parseFloat(item.split_percentage) * 100).toFixed(0) : 0;
                return item;
            },

            setItems(data) {
                if (data) {
                    this.items = data.map(x => this.castItem(x))
                } else {
                    this.items = [];
                }
            },

            save() {
                let form = new Form({
                    payers: this.items,
                });
                form.patch(`/business/clients/${this.client.id}/payers`)
                    .then( ({ data }) => {
                        this.setItems(data.data);
                    })
                    .catch(e => {
                    })
            },
        },

        async mounted() {
            this.setItems(this.payers);
        },
    }
</script>

<style>

</style>
