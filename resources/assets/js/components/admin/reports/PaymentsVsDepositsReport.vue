<template>
    <b-card header="Payments vs Deposits Report"
        header-text-variant="white"
        header-bg-variant="info"
    >
        <b-row>
            <b-col lg="12">
                <b-form inline class="mb-4">
                    <date-picker
                        v-model="filters.start_date"
                        placeholder="Start Date"
                        class="mt-1"
                    />
                        &nbsp;to&nbsp;
                    <date-picker
                        v-model="filters.end_date"
                        placeholder="End Date"
                        class="mr-1 mt-1"
                    />

                    <b-btn variant="info" class="mt-1" :disabled="filters.busy" @click.prevent="fetch()">Generate</b-btn>
                </b-form>
            </b-col>
        </b-row>

        <b-row class="mb-2">
            <b-col lg="6">
                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
            <b-col lg="6" class="d-flex">
                <b-button @click="download()" v-if="!filters.busy && !!items" variant="success" class="ml-auto">
                    <i class="fa fa-file-excel-o"></i> Export to Excel
                </b-button>
            </b-col>
        </b-row>

        <loading-card v-if="filters.busy" />
        <div v-else class="table-responsive">
            <b-table bordered striped hover show-empty
                :items="items"
                :fields="fields"
                :sort-by.sync="sortBy"
                :sort-desc.sync="sortDesc"
                :filter="filter"
                :empty-text="emptyText"
            >
                <template slot="diff" scope="row">
                    {{ moneyFormat(row.item.diff) }} ({{ numberFormat(row.item.diff_percent) }}%)
                </template>
            </b-table>
        </div>
    </b-card>
</template>

<script>
    import FormatsStrings from "../../../mixins/FormatsStrings";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsDates from "../../../mixins/FormatsDates";
    import Constants from '../../../mixins/Constants';

    export default {
        mixins: [FormatsDates, FormatsNumbers, Constants, FormatsStrings],

        data() {
            return {
                items: [],
                sortBy: 'chain',
                sortDesc: false,
                filter: '',
                fields: {
                    chain: { label: 'Business Chain', sortable: true, },
                    payments: { sortable: true, formatter: x => this.moneyFormat(x) },
                    deposits: { sortable: true, formatter: x => this.moneyFormat(x) },
                    diff: { sortable: true },
                },
                filters: new Form({
                    start_date: moment().subtract(7, 'days').format('MM/DD/YYYY'),
                    end_date: moment().format('MM/DD/YYYY'),
                    json: 1,
                }),
            }
        },

        computed: {
            emptyText() {
                return this.filters.hasBeenSubmitted ? 'There are no results to display.' : 'Select filters and press Generate.';
            }
        },

        methods: {
            async fetch() {
                this.filters.get(`/admin/reports/payments-vs-deposits`)
                    .then( ({ data }) => {
                        this.items = data;
                    })
                    .catch(() => {
                        this.items = [];
                    });
            },

            download() {
                window.location = this.filters.toQueryString('/admin/reports/payments-vs-deposits?export=1');
            },
        },

        async mounted() {
            this.fetch();
        }
    }
</script>
