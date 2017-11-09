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
                            {{ formatDate(item.checked_in_time) }}
                        </td>
                        <td>
                            {{ formatTime(item.checked_in_time) }} - {{ formatTime(item.checked_out_time) }}
                        </td>
                        <td>
                            <span v-for="activity in item.activities" :key="activity.id">{{ activity.name }}</span>
                        </td>
                        <td>
                            {{ item.caregiver.name }}
                        </td>
                        <td>
                            {{ item.caregiver_rate }}
                        </td>
                        <td>
                            {{ item.hours_type }}
                        </td>
                        <td>
                            {{ item.mileage }}
                        </td>
                        <td>
                            {{ item.roundedShiftLength }}
                        </td>
                        <td>
                            {{ item.roundedShiftLength * item.caregiver_rate }}
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

<style lang="scss">
</style>

<script>
    export default {
        props: ['payment'],

        data() {
            return {
                items: this.payment.shifts,
                fields: [
                    { key: 'checked_in_time', label: 'Date' },
                    { key: 'care_time', label: 'Time' },
                    { key: 'activities', label: 'Activities Performed' },
                    { key: 'roundedShiftLength', label: 'Hours of Care Received' },
                    { key: 'caregiver', label: 'Caregiver' },
                    { key: 'amount', label: 'Amount' }
                ]
            }
        },

        created() {

        },

        mounted() {

        },

        methods: {
            formatDate(date) {
                return moment(date).format('L');
            },

            formatTime(dateTime) {
                return moment(dateTime).format('h:mm:ss a');
            }
        },

        computed: {
            total() {
               return _.reduce(this.items, function(sum, item) {
                    return sum + item.caregiver_rate * item.roundedShiftLength;
                }, 0);
            }
        }
    }
</script>