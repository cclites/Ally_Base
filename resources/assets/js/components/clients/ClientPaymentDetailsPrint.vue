<template>
    <b-container>
        <b-row class="pt-5" align-h="between">
            <b-col cols="4">
                <div>{{ payment.business.name }}</div>
                <div>{{ payment.business.address1 }}</div>
                <div v-if="payment.business.address2">{{ payment.business.address2 }}</div>
                <span>{{ payment.business.city }}</span>,
                <span>{{ payment.business.state }}</span>
                <span>{{ payment.business.zip }}</span>
                <div>{{ payment.business.phone1 }}</div>
            </b-col>
            <b-col cols="4">
                <div>Care Services Statement For:</div>
                <div>{{ payment.client.name }}</div>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <table class="table table-bordered mt-2">
                    <thead>
                    <tr>
                        <th colspan="4"></th>
                        <th colspan="4">Rates</th>
                        <th></th>
                    </tr>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Activities Performed</th>
                        <th>Caregiver</th>
                        <th>Rate</th>
                        <th>Hours Type</th>
                        <th>Mileage</th>
                        <th>Hours</th>
                        <th>Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in items" :key="item.id">
                        <td>
                            {{ formatDateFromUTC(item.checked_in_time) }}
                        </td>
                        <td>
                            {{ formatTimeFromUTC(item.checked_in_time) }} - {{ formatTimeFromUTC(item.checked_out_time) }}
                        </td>
                        <td>
                            <div v-for="activity in activities(item.activities)" :key="activity">{{ activity }}</div>
                        </td>
                        <td>
                            {{ item.caregiver.name }}
                        </td>
                        <td>
                            {{ moneyFormat(item.hourly_total) }}
                        </td>
                        <td>
                            {{ item.hours_type }}
                        </td>
                        <td>
                            {{ item.mileage }}
                        </td>
                        <td>
                            {{ item.duration }}
                        </td>
                        <td>
                            {{ moneyFormat(item.shift_total) }}
                        </td>
                    </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="7"></td>
                        <td>
                            Total:
                        </td>
                        <td>
                            {{ total }}
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </b-col>
        </b-row>
    </b-container>
</template>

<script>
    import FormatsDates from '../../mixins/FormatsDates';
    import FormatsNumbers from '../../mixins/FormatsNumbers';

    export default {
        props: ['payment'],

        mixins: [FormatsDates, FormatsNumbers],

        data() {
            return {
                items: this.payment.shifts
            }
        },

        computed: {
            total() {
               return this.moneyFormat(_.reduce(this.items, function(sum, item) {
                    return sum + parseFloat(item.shift_total);
                }, 0));
            }
        },

        methods: {
            activities(activities) {
                return _.uniq(_.map(_.sortBy(activities, 'name'), 'name'));
            }
        }
    }
</script>