<template>
    <div>
        <b-card>
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
                <b-table id="caregiver_payment_history" :items="items" :fields="fields" foot-clone>
                    <template slot="created_at" scope="data">
                        {{ formatDate(data.item.created_at) }}
                    </template>
                    <template slot="week" scope="row">
                    <span v-if="row.item.adjustment">
                        Manual Adjustment
                    </span>
                        <span v-else>
                        {{ formatDate(row.item.week.start) }} - {{ formatDate(row.item.week.end) }}
                    </span>
                    </template>
                    <template slot="success" scope="data">
                        <span style="color: green;" v-if="data.value">Complete</span>
                        <span style="color: darkred;" v-else>Failed</span>
                    </template>
                    <template slot="actions" scope="data">
                        <b-btn :href="'/business/reports/caregivers/payment-history/' + data.item.id + '/print/' + caregiver.id" class="btn btn-secondary">View Details</b-btn>
                        <b-btn :href="'/business/reports/caregivers/payment-history/' + data.item.id + '/print/' + caregiver.id + '?type=pdf'" class="btn btn-secondary" v-if="!isMobileApp">Download</b-btn>
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
        </b-card>
    </div>
</template>

<script>
    import FormatsDates from '../../../mixins/FormatsDates';
    import FormatsNumbers from '../../../mixins/FormatsNumbers';
    import moment from 'moment';
    import MobileApp from "../../../mixins/MobileApp";
    export default {
        props: ['caregiver', 'deposits'],

        mixins: [FormatsDates, FormatsNumbers, MobileApp],

        data() {
            return {
                selectedYear: moment().year(),
                fields: [
                    { key: 'created_at', label: 'Paid' },
                    { key: 'week', label: 'Shifts Added'},
                    { key: 'success', label: 'Deposit Status' },
                    {
                        key: 'amount',
                        formatter: (value) => { return this.moneyFormat(value); }
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ]
            }
        },

        methods: {
            printSummary() {
                window.location = '/business/reports/caregivers/' + this.caregiver.id + '/payment-history/print/' + this.selectedYear;
            }
        },

        computed: {
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
        }
    }
</script>
