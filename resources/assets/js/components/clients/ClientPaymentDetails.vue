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
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Care Date</th>
                    <th>Care Time</th>
                    <th>Hours of Care Received</th>
                    <th>Caregiver Name</th>
                    <th>Activities</th>
                    <th>Amount</th>
                </tr>
                </thead>
                <tbody>
                <tr v-if="payment.adjustment">
                    <td>{{ formatDateFromUTC(payment.created_at) }}</td>
                    <td>Manual Adjustment</td>
                    <td colspan="3">{{ payment.notes }}</td>
                    <td>{{ this.moneyFormat(payment.amount) }}</td>
                </tr>
                <tr v-for="shift in shifts">
                    <td>{{ formatDateFromUTC(shift.checked_in_time.date) }}</td>
                    <td>
                        {{ formatTimeFromUTC(shift.checked_in_time.date) }} - {{ formatTimeFromUTC(shift.checked_out_time.date) }}
                    </td>
                    <td>{{ shift.hours }}</td>
                    <td>{{ shift.caregiver_name }}</td>
                    <td>
                        <div v-for="activity in shift.activities" :key="activity">{{ activity }}</div>
                    </td>
                    <td>
                        {{ moneyFormat(shift.shift_total) }}
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
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