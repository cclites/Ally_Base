<template>
    <div class="table-responsive">
        <b-row>
            <b-col lg="3">
                <b-form-group label="Year">
                    <b-form-select v-model="filters.year">
                        <option v-for="year in years" :value="year" :key="year">{{ year }}</option>
                    </b-form-select>
                </b-form-group>
            </b-col>
        </b-row>
        <b-table hover :foot-clone="items.length !== 0" show-empty
                 :sort-by="sortBy"
                 :sort-desc="sortDesc"
                 :items="items"
                 :fields="fields">
            <template slot="status" scope="data">
                <span style="color: green;" v-if="data.value">Complete</span>
                <span style="color: darkred;" v-else>Failed</span>
            </template>
            <template slot="actions" scope="data">
                <div v-if="isOfficeUserOrAdmin" >
                    <a :href="'/business/client/payments/' + data.item.id" class="btn btn-secondary" target="_blank">
                        <i class="fa fa-external-link"></i> View
                    </a>
                    <a :href="'/business/client/payments/' + data.item.id + '/pdf'" class="btn btn-secondary">
                        <i class="fa fa-file-pdf-o"></i> Download
                    </a>
                </div>
                <div v-else>
                    <a :href="'/client/payments/' + data.item.id" class="btn btn-secondary" target="_blank">
                        <i class="fa fa-external-link"></i> View
                    </a>
                    <a :href="'/client/payments/' + data.item.id + '/pdf'" class="btn btn-secondary">
                        <i class="fa fa-file-pdf-o"></i> Download
                    </a>
                </div>
            </template>
            <template slot="invoices" scope="data">
                <div v-for="invoice in data.item.invoices" :key="invoice.id">
                    <a v-if="isOfficeUserOrAdmin" :href="`/business/client/invoices/${invoice.id}`" target="_blank">#{{ invoice.name }}</a>
                    <a v-else :href="`/client/invoices/${invoice.id}`" target="_blank">#{{ invoice.name }}</a>
                </div>
            </template>
            <template slot="FOOT_payment_date" scope="data">
                Total {{ filters.year }}
            </template>
            <template slot="FOOT_amount" scope="data">
                {{ moneyFormat(total) }}
            </template>
            <template slot="FOOT_status" scope="data">
                -
            </template>
            <template slot="FOOT_invoices" scope="data">
                -
            </template>
            <template slot="FOOT_actions" scope="data">
                <b-btn @click="printSummary()">Print Year Summary</b-btn>
            </template>
        </b-table>
    </div>
</template>

<script>
    import FormatsDates from '../../mixins/FormatsDates';
    import FormatsNumbers from '../../mixins/FormatsNumbers';
    import AuthUser from '../../mixins/AuthUser';

    export default {
        props: ['client'],
        mixins: [FormatsDates, FormatsNumbers, AuthUser],

        computed: {
            years() {
                let years = [];
                for (let i = 0; i < 5; i++) {
                    years.push(moment().subtract(i, 'years').year())
                }
                return years;
            },
        },

        data() {
            return {
                total: 0.00,
                filters: new Form({
                    year: moment().year(),
                    json: 1,
                }),
                items: [],
                fields: {
                    payment_date: { label: 'Date Paid', sortable: true, formatter: x => this.formatDateFromUTC(x) },
                    amount: { sortable: true, formatter: x => this.moneyFormat(x) },
                    status: { label: 'Payment Status', sortable: true, formatter: x => x ? 'Paid' : 'Failed' },
                    invoices: { label: 'Related Invoices'},
                    actions: { sortable: false, class: 'hidden-print' },
                },
                sortBy: 'payment_date',
                sortDesc: true,
            }
        },

        methods: {
            async fetch() {
                let url = `/client/payments`;
                if (this.isOfficeUserOrAdmin) {
                    url = `/business/clients/${this.client.id}/payment-history`;
                }

                this.filters.get(url)
                    .then( ({ data }) => {
                        this.items = data.rows;
                        this.total = data.total;
                    })
                    .catch(() => {
                    });
            },

            printSummary() {
                if (this.isOfficeUserOrAdmin) {
                    window.location = `/business/clients/${this.client.id}/payment-history/print?year=${this.filters.year}`;
                } else {
                    window.location = `/client/payment-summary/print/?year=${this.filters.year}`;
                }
            },
        },

        mounted() {
            this.fetch();
        },

        watch: {
            'filters.year'(newVal, oldVal) {
                this.fetch();
            },
        },
    }
</script>
