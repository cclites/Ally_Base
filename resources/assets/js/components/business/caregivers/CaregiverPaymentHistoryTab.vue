<template>
    <b-card header="Payment History"
            header-text-variant="white"
            header-bg-variant="info">
        <b-table :items="items" :fields="fields">
            <template slot="action" scope="row">
                <b-btn @click="toggleDetails(row.item)" :pressed="row.item._showDetails"
                       :class="{ 'text-white': row.item._showDetails }">
                    Details
                </b-btn>
            </template>
            <template slot="row-details" scope="row">
                <b-table :items="row.item.shifts" :fields="shiftFields" foot-clone outlined dark>
                    <template slot="activities" scope="row">
                        {{ activities(row.item) }}
                    </template>
                    <template slot="FOOT_checked_in_time" scope="data"></template>
                    <template slot="FOOT_checked_out_time" scope="data"></template>
                    <template slot="FOOT_activities" scope="data"></template>
                    <template slot="FOOT_duration" scope="data">
                        <!-- A custom formatted footer cell  for field 'name' -->
                        <strong>Total Hours: {{ totalHours(row.item.shifts) }}</strong>
                    </template>
                </b-table>
            </template>
        </b-table>
    </b-card>
</template>

<script>
    import FormatsNumbers from '../../../mixins/FormatsNumbers';
    import FormatsDates from '../../../mixins/FormatsDates';

    export default {
        props: ['caregiver'],

        mixins: [FormatsNumbers, FormatsDates],

        components: {},

        data() {
            return {
                fields: [
                    {
                        key: 'created_at',
                        formatter: value => {
                            return this.formatDate(value)
                        }
                    },
                    {
                        key: 'amount',
                        formatter: value => {
                            return this.moneyFormat(value)
                        }
                    },
                    'action'
                ],
                shiftFields: [
                    {
                        key: 'checked_in_time',
                        formatter: value => {
                            return this.formatDateTime(value)
                        }
                    },
                    {
                        key: 'checked_out_time',
                        formatter: value => {
                            return this.formatDateTime(value)
                        }
                    },
                    {
                        key: 'activities'
                    },
                    {
                        key: 'duration',
                        label: 'Hours'
                    }
                ]
            }
        },

        methods: {
            toggleDetails(item) {
                item._showDetails = !item._showDetails;
            },

            totalHours(shifts) {
                return _.sumBy(shifts, shift => {
                    return parseFloat(shift.duration);
                });
            },

            activities(shift) {
                return _.join(_.uniq(_.map(shift.activities, 'name')), ', ');
            }
        },

        computed: {
            items() {
                return _.map(this.caregiver.deposits, deposit => {
                    deposit._showDetails = false;
                    return deposit;
                });
            }
        }
    }
</script>