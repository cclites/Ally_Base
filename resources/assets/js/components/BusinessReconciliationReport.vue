<template>
    <b-card
        header="Reconciliation Report"
        header-text-variant="white"
        header-bg-variant="info"
        >
        <div class="text-right">
            <b-btn href="/business/reports/reconciliation?export=1" variant="success"><i class="fa fa-file-excel-o"></i> Export to Excel</b-btn>
            <b-btn href="javascript:print()" variant="primary"><i class="fa fa-print"></i> Print</b-btn>
        </div>
        <b-table bordered striped hover show-empty
                 :fields="fields"
                 :items="items"
                 :sort-by.sync="sortBy"
                 :sort-desc.sync="sortDesc"
                 class="shift-table"
        >
            <template slot="created_at" scope="data">
                {{ formatDate(data.value) }}
            </template>
            <template slot="amount_deposited" scope="data">
                {{ numberFormat(data.value) }}
            </template>
            <template slot="amount_withdrawn" scope="data">
                {{ numberFormat(data.value) }}
            </template>
            <template slot="actions" scope="row">
                <b-btn size="sm" :href="'/business/transactions/' + row.item.id">View Transaction Details</b-btn>
            </template>
        </b-table>
    </b-card>
</template>

<script>
    import FormatsDates from '../mixins/FormatsDates'
    import FormatsNumbers from '../mixins/FormatsNumbers'

    export default {
        mixins: [
            FormatsDates,
            FormatsNumbers
        ],

        props: {},

        data() {
            return {
                'sortBy': 'created_at',
                'sortDesc': true,
                'items': [],
                'fields': [
                    {
                        key: 'created_at',
                        label: 'Date',
                        sortable: true,
                    },
                    {
                        key: 'amount_deposited',
                        label: 'Deposited',
                        sortable: true,
                    },
                    {
                        key: 'amount_withdrawn',
                        label: 'Withdrawn',
                        sortable: true,
                    },
                    'actions'
                ]
            }
        },

        mounted() {
            this.loadData();
        },

        methods: {
            loadData() {
                axios.get('/business/reports/reconciliation?json=1')
                    .then(response => {
                        if (Array.isArray(response.data)) {
                            this.items = response.data;
                        }
                        else {
                            this.items = [];
                        }
                    });
            }
        },
    }
</script>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .shift-table, .shift-table * {
            visibility: visible;
        }
        .shift-table {
            position: absolute;
            left: 0;
            top: 0;
        }
    }
</style>