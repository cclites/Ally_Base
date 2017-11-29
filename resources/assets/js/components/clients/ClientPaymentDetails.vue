<template>
    <b-card title="Payment Details">
        <b-row>
            <b-col>
                <div class="pull-right">
                    <a :href="'/payment-history/' + payment.id + '/print'" target="_blank">
                        Print
                    </a>
                </div>
            </b-col>
        </b-row>
        <b-table hover
                 :items="items"
                 :fields="fields">
            <template slot="checked_in_time" scope="data">
                {{ formatDate(data.item.checked_in_time) }}
            </template>
            <template slot="care_time" scope="data">
                {{ formatTime(data.item.checked_in_time) }} - {{ formatTime(data.item.checked_out_time) }}
            </template>
            <template slot="activities" scope="data">
                <div v-for="activity in activities(data.item.activities)" :key="activity">{{ activity }}</div>
            </template>
            <template slot="caregiver_name" scope="data">
                {{ data.item.caregiver.name }}
            </template>
        </b-table>
    </b-card>
</template>

<script>
    import FormatsDates from '../../mixins/FormatsDates';
    import FormatsNumbers from '../../mixins/FormatsNumbers';

    export default {
        props: ['payment'],

        mixins: [FormatsDates, FormatsNumbers],

        data() {
            return {
                items: this.payment.shifts,
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
                        key: 'roundedShiftLength',
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
            activities(activities) {
                return _.uniq(_.map(_.sortBy(activities, 'name'), 'name'));
            }
        }
    }
</script>