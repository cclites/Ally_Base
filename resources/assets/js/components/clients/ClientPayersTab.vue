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
                <template slot="effective_start" scope="row">
                    {{ formatDate(row.item.effective_start, 'MM/DD/YYYY', 'YYYY-MM-DD') }}
                </template>
                <template slot="effective_end" scope="row">
                    {{ formatDate(row.item.effective_end, 'MM/DD/YYYY', 'YYYY-MM-DD') }}
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

    export default {
        props: {
            'client': {},
            'payers': Array,
            'payerOptions': Array,
        },

        mixins: [ FormatsDates ],

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
                        key: 'policy_number',
                        label: 'Policy Number',
                        sortable: true,
                    },
                    {
                        key: 'policy_number',
                        label: 'Policy Number',
                        sortable: true,
                    },
                    {
                        key: 'policy_number',
                        label: 'Policy Number',
                        sortable: true,
                    },
                    {
                        key: 'policy_number',
                        label: 'Policy Number',
                        sortable: true,
                    },
                    {
                        key: 'policy_number',
                        label: 'Policy Number',
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
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
                let item = this.items.find(x => x.id === data.id);
                if (item) {
                    item.payer_id = data.payer_id;
                    item.policy_number = data.policy_number;
                    item.effective_start = data.effective_start;
                    item.effective_end = data.effective_end;
                    item.payment_allocation = data.payment_allocation;
                    item.payment_allowance = data.payment_allowance;
                    item.split_percentage = data.split_percentage;
                    item.priority = data.priority;
                    item.client_id = data.client_id;
                    item.payer_name = data.payer_name;
                } else {
                    this.items.push(data);
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
        },

        mounted() {
            this.items = this.payers;
        },
    }
</script>