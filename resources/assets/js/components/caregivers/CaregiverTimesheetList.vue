<template>
    <div>
        <b-card>
            <div class="table-responsive">
                <b-table :items="items" :fields="fields" foot-clone>
                    <template slot="actions" scope="data">
                        <b-btn :href="'/timesheets/' + data.item.id" class="btn btn-secondary">View</b-btn>
                    </template>
                </b-table>
            </div>
        </b-card>
    </div>
</template>

<script>
    import DashboardMetric from '../DashboardMetric';
    import FormatsDates from '../../mixins/FormatsDates';
    import FormatsNumbers from '../../mixins/FormatsNumbers';
    import moment from 'moment';
    import MobileApp from "../../mixins/MobileApp";
    export default {
        props: ['caregiver', 'timesheets'],

        mixins: [FormatsDates],

        data() {
            return {
                items: this.timesheets.map(item => {
                    let entry = item.entries[0];
                    if (entry) {
                        item.week = moment.utc(entry.checked_in_time).startOf('week').format('MM/DD/YYYY') + ' - ' +
                                moment.utc(entry.checked_in_time).endOf('week').format('MM/DD/YYYY');
                    }
                    item.shift_count = item.entries.length;
                    return item;
                }),
                fields: [
                    { key: 'created_at', label: 'Created Date', formatter: (val) => this.formatDateFromUTC(val) },
                    { key: 'week' },
                    { key: 'shift_count' },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ]
            }
        },

        methods: {

        },

        computed: {

        }
    }
</script>
