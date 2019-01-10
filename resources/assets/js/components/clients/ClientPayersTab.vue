<template>
    <b-card
        header="Client Payers"
        header-text-variant="white"
        header-bg-variant="info"
        >
        <b-btn variant="info" class="mb-2" @click="add()">Add Client Payer</b-btn>

        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                :items="items"
                :fields="fields"
                :current-page="currentPage"
                :per-page="perPage"
                :sort-by.sync="sortBy"
                :sort-desc.sync="sortDesc"
                ref="table"
            >
                <template slot="priority" scope="row">
                    <b-btn size="sm" @click="shiftPriority(row.item, -1)" :disabled="row.item.priority <= 1">
                        <i class="fa fa-arrow-up"></i>
                    </b-btn>
                </template>
                <template slot="payment_allocation" scope="row">
                    {{ stringFormat(row.item.payment_allocation) }}
                    <span v-if="row.item.payment_allocation == 'split'">
                        ({{ row.item.split_percentage }}%)
                    </span>
                    <span v-if="['daily', 'weekly', 'monthly'].includes(row.item.payment_allocation)">
                        (${{ parseFloat(row.item.payment_allowance).toFixed(2) }})
                    </span>
                </template>
                <template slot="actions" scope="row">
                    <b-btn size="sm" @click="edit(row.item)">
                        <i class="fa fa-edit"></i>
                    </b-btn>
                    <b-btn size="sm" @click="remove(row.item)">
                        <i class="fa fa-trash"></i>
                    </b-btn>
                </template>
            </b-table>
        </div>

        <client-payer-modal 
            @saved="onSave"
            v-model="showModal" 
            :source="payer"
            :payers="payerOptions"
        />
    </b-card>
</template>

<script>
    import FormatsDates from "../../mixins/FormatsDates";
    import FormatsStrings from "../../mixins/FormatsStrings";

    export default {
        props: {
            'client': {},
            'payers': Array,
            'payerOptions': Array,
        },

        mixins: [ FormatsDates, FormatsStrings ],

        data() {
            return {
                showModal: false,
                payer: {},
                items: [],
                totalRows: 0,
                perPage: 30,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                fields: [
                    {
                        key: 'priority',
                        label: 'Priority',
                        sortable: true,
                    },
                    {
                        key: 'payer_name',
                        label: 'Payer',
                        sortable: true,
                    },
                    {
                        key: 'policy_number',
                        label: 'Policy Number',
                        sortable: true,
                    },
                    {
                        key: 'effective_start',
                        label: 'Effective Start',
                        sortable: true,
                    },
                    {
                        key: 'effective_end',
                        label: 'Effective End',
                        sortable: true,
                    },
                    {
                        key: 'payment_allocation',
                        label: 'Payment Allocation',
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print',
                        sortable: false,
                    },
                ],
            }
        },

        computed: {
        },

        methods: {
            add() {
                this.payer = {
                    id: null,
                    payer_id: '',
                    policy_number: '',
                    effective_start: moment().format('MM/DD/YYYY'),
                    effective_end: moment('9999-12-31').format('MM/DD/YYYY'),
                    payment_allocation: 'balance',
                    payment_allowance: '0.00',
                    split_percentage: '0',
                    client_id: this.client.id,
                };
                this.showModal = true;
            },

            onSave(data) {
                let index = this.items.findIndex(item => item.id == data.id);
                if (index != -1) {
                    this.items.splice(index, 1, this.castItem(data));
                } else {
                    this.items.push(this.castItem(data));
                }
            },

            remove(item) {
                if (confirm('Are you sure you want to remove this Payer from the Client?')) {
                    let form = new Form();
                    form.submit('delete', `/business/clients/${this.client.id}/payers/${item.id}`)
                        .then( ({ data }) => {
                            this.items = this.items.filter(x => x.id !== item.id);
                        });
                }
            },

            edit(item) {
                this.payer = {};
                this.payer = item;
                this.showModal = true;
            },

            shiftPriority(item, upOrDown = -1) {
                let form = new Form({ priority: item.priority + upOrDown });
                form.submit('patch', `/business/clients/${this.client.id}/payers/${item.id}/priority`)
                    .then( ({ data }) => {
                        this.setItems(data);
                    });
            },
            
            setItems(items) {
                this.items = items.map(item => {
                    return this.castItem(item);
                })
            },

            castItem(data) {
                let item = JSON.parse(JSON.stringify(data));
                item.payer = item.payer ? item.payer : '';
                item.effective_start = moment(item.effective_start, 'YYYY-MM-DD').format('MM/DD/YYYY');
                item.effective_end = moment(item.effective_end, 'YYYY-MM-DD').format('MM/DD/YYYY');
                item.split_percentage = item.split_percentage ? (parseFloat(item.split_percentage) * 100).toFixed(0) : 0;
                return item;
            },
        },

        mounted() {
            this.setItems(this.payers);
        },
    }
</script>