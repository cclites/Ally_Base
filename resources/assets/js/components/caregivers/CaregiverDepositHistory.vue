<template>
    <div>
        <b-row>
            <b-col lg="3">
                <b-form-group label="Year">
                    <b-form-select v-model="selectedYear">
                        <option v-for="year in years" :value="year" :key="year">{{ year }}</option>
                    </b-form-select>
                </b-form-group>
            </b-col>
        </b-row>
        <div class="table-responsive">
            <b-table hover :foot-clone="items.length !== 0" show-empty
                     :sort-by="sortBy"
                     :sort-desc="sortDesc"
                     :items="items"
                     :fields="fields">
                <template slot="created_at" scope="data">
                    {{ formatDate(data.item.created_at) }}
                </template>
                <template slot="week" scope="data">
                    {{ start_end(data) }}
                </template>
                <template slot="success" scope="data">
                    <span style="color: green;" v-if="data.value">Complete</span>
                    <span style="color: darkred;" v-else>Failed</span>
                </template>
                <template slot="actions" scope="data">
                    <slot name="actions" :item="data.item">
                        <a :href="depositUrl(data.item)" class="btn btn-secondary" target="_blank">
                            <i class="fa fa-external-link"></i> View
                        </a>
                        <a :href="depositUrl(data.item, 'pdf')" class="btn btn-secondary">
                            <i class="fa fa-file-pdf-o"></i> Download
                        </a>
                    </slot>
                </template>
                <template slot="FOOT_created_at" scope="data">
                    Total YTD
                </template>
                <template slot="FOOT_week" scope="data">
                    {{ selectedYear }}
                </template>
                <template slot="FOOT_success" scope="data">
                    -
                </template>
                <template slot="FOOT_amount" scope="data">
                    {{ total }}
                </template>
                <template slot="FOOT_actions" scope="data">
                    <b-btn @click="printSummary()" v-if="!isMobileApp">Print Year Summary</b-btn>
                </template>
            </b-table>
        </div>
    </div>
</template>

<script>
    import MobileApp from "../../mixins/MobileApp";
    import FormatsDates from '../../mixins/FormatsDates';
    import FormatsNumbers from '../../mixins/FormatsNumbers';
    import AuthUser from '../../mixins/AuthUser';

    export default {
        props: ['caregiver', 'deposits', 'urlGenerator'],

        mixins: [FormatsDates, FormatsNumbers, MobileApp, AuthUser],

        data() {
            return {
                selectedYear: moment().year(),
                fields: [
                    { key: 'created_at', label: 'Date Paid', sortable: true },
                    { key: 'week', label: 'Week' },
                    { key: 'success', label: 'Payment Status' },
                    {
                        key: 'amount',
                        label: 'Amount',
                        formatter: (value) => { return this.moneyFormat(value) },
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'                        
                    }
                ],
                sortBy: 'created_at',
                sortDesc: true,
            }
        },

        computed: {
            depositUrl() {
                if (this.urlGenerator) return this.urlGenerator;
                return (deposit, view="") =>  `/caregiver/deposits/${deposit.id}/${view}`;
            },

            items() {
                return _.filter(this.deposits, (deposit) => {
                    return moment(deposit.created_at).year() === this.selectedYear;
                })
            },

            total() {
                let items = this.items.filter(item => item.success === 1);
                items = _.map(items, (value) => {
                    value.amount = parseFloat(value.amount);
                    return value;
                });
                return this.moneyFormat(_.sumBy(items, 'amount'));
            },

            years() {
                let years = [];
                for (let i = 0; i < 5; i++) {
                    years.push(moment().subtract(i, 'years').year())
                }
                return years;
            }
        },

        methods: {
            start_end(data) {
                if (data.item.week) {
                    if ('start' in data.item.week) {
                        return `${this.formatDate(data.item.week.start)} - ${this.formatDate(data.item.week.end)}`;
                    }
                }
                return 'Shift N/A';
            },

            printSummary() {
                if (this.isOfficeUserOrAdmin) {
                    window.location = '/business/reports/caregivers/' + this.caregiver.id + '/payment-history/print/' + this.selectedYear;
                } else {
                    window.location = '/reports/payment-history/print/' + this.selectedYear;
                }
            },
        }
    }
</script>
