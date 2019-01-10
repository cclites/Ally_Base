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
            ></b-table>
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
                        key: 'name',
                        label: 'Name',
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
                };
                this.showModal = true;
            },

            onSave() {

            },
        },

        mounted() {
        },
    }
</script>