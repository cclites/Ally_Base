<template>
    <div>
        <b-card header="Pay Statements"
                header-bg-variant="info"
                header-text-variant="white"
        >
            <caregiver-deposit-history :deposits="deposits" :urlGenerator="depositUrl" />
        </b-card>
    </div>
</template>

<script>
    import FormatsDates from '../../../mixins/FormatsDates';
    import FormatsNumbers from '../../../mixins/FormatsNumbers';
    import moment from 'moment';
    export default {
        props: ['caregiver', 'deposits'],

        mixins: [FormatsDates, FormatsNumbers],

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
            },

            depositUrl(deposit, view="") {
                return `/business/statements/deposits/${deposit.id}/${view}`;
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
