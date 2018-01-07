<template>
    <b-card title="Payment Details">
        <b-row>
            <b-col>
                <div class="pull-right">
                    <a :href="printUrl()" target="_blank">
                        Print
                    </a>
                </div>
            </b-col>
        </b-row>
        <b-table hover
                 :items="items"
                 :fields="fields">
            <template slot="checked_in_time" scope="data">
                {{ formatDateFromUTC(data.item.checked_in_time.date) }}
            </template>
            <template slot="care_time" scope="data">
                {{ formatTimeFromUTC(data.item.checked_in_time.date) }} - {{ formatTimeFromUTC(data.item.checked_out_time.date) }}
            </template>
            <template slot="activities" scope="data">
                <div v-for="activity in data.item.activities" :key="activity">{{ activity }}</div>
            </template>
        </b-table>
    </b-card>
</template>

<script>
    import FormatsDates from '../../mixins/FormatsDates';
    import FormatsNumbers from '../../mixins/FormatsNumbers';

    export default {
        props: ['payment', 'shifts', 'roleType'],

        mixins: [FormatsDates, FormatsNumbers],

        data() {
            return {
                items: this.shifts,
                fields: [
                    {
                        key: 'checked_in_time',
                        label: 'Care Date'
                    },
                    {
                        key: 'care_time',
                        label: 'Care Time'
                    },
                    {
                        key: 'hours',
                        label: 'Hours of Care Received'
                    },
                    {
                        key: 'caregiver_name'
                    },
                    {
                        key: 'activities'
                    },
                    {
                        key: 'shift_total',
                        label: 'Amount',
                        formatter: (value) => { return this.moneyFormat(value) }
                    }
                ]
            }
        },

        methods: {

            printUrl() {
                switch (this.roleType) {
                    case 'client':
                        return '/payment-history/' + this.payment.id + '/print';
                    case 'office_user':
                        return '/business/clients/payments/' + this.payment.id + '/print';
                }
            }

        }
    }
</script>