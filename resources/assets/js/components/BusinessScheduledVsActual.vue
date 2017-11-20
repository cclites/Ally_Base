<template>
    <div>
        <b-row>
            <b-col lg="12">
                <b-card
                        header="Select Date Range"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-form inline @submit.prevent="loadData()">
                        <date-picker
                                v-model="start_date"
                                placeholder="Start Date"
                        >
                        </date-picker> &nbsp;to&nbsp;
                        <date-picker
                                v-model="end_date"
                                placeholder="End Date"
                        >
                        </date-picker>
                        &nbsp;&nbsp;<b-button type="submit" variant="info">Generate Report</b-button>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>
        <b-row>
            <b-col lg="12">
                <b-card
                        header="Missing Shifts"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-table bordered striped hover show-empty
                             :fields="fields"
                             :items="items"
                             :sort-by.sync="sortBy"
                             :sort-desc.sync="sortDesc"
                             class="shift-table"
                    >
                        <template slot="Day" scope="data">
                            {{ dayFormat(data.value) }}
                        </template>
                        <template slot="actions" scope="row">
                            <div v-if="!row.item.converted">
                                <b-btn @click="convertToShift(row.item)" >Convert</b-btn>
                            </div>
                            <div v-else>
                                <b-btn disabled>Converted</b-btn>
                                <b-btn :href="'/business/shifts/' + row.item.shift_id">Edit</b-btn>
                            </div>
                        </template>
                    </b-table>
                </b-card>
            </b-col>
        </b-row>
        <business-convert-schedule-modal
                v-model="convertModal"
                :selectedItem="selectedItem"
                @convert="markConverted"
        ></business-convert-schedule-modal>
    </div>
</template>

<script>
    export default {
        props: {},

        data() {
            return {
                items: [],
                start_date: moment().startOf('isoweek').format('MM/DD/YYYY'),
                end_date: moment().startOf('isoweek').add(6, 'days').format('MM/DD/YYYY'),
                sortBy: null,
                sortDesc: null,
                selectedItem: {},
                convertModal: false,
            }
        },

        mounted() {
            this.loadData();
        },

        computed: {
            fields() {
                let fields = [];
                let item;
                if (item = this.items[0]) {
                    for (let key of Object.keys(item)) {
                        if (key === 'key') continue;
                        if (key === 'schedule_id') continue;
                        if (key === 'shift_id') continue;
                        if (key === 'converted') continue;
                        fields.push({
                            'key': key,
                            'sortable': true,
                        });
                    }
                }
                fields.push('actions');
                return fields;
            },
        },

        methods: {
            loadData() {
                let component = this;
                axios.get('/business/reports/scheduled_vs_actual?json=1&start_date=' + this.start_date + '&end_date=' + this.end_date)
                    .then(function(response) {
                        if (Array.isArray(response.data)) {
                            let counter = 1;
                            component.items = response.data.map(function(item) {
                                return {
                                    'key': counter++,
                                    'schedule_id': item.schedule_id,
                                    'converted': false,
                                    //
                                    'Day': item.start,
                                    'Time': moment(item.start).format('h:mm A') + ' - ' + moment(item.end).format('h:mm A'),
                                    'Hours': item.hours,
                                    'Client': (item.client) ? item.client.nameLastFirst : '',
                                    'Caregiver': (item.caregiver) ? item.caregiver.nameLastFirst : '',
                                    'CG Rate': item.caregiver_rate,
                                    'Reg Rate': item.provider_fee,
                                    'Ally Fee': item.ally_fee,
                                    'Total Hourly': item.hourly_total,
                                    'Shift Total': item.shift_total,
                                    'Type': item.hours_type,
                                }
                            });
                        }
                        else {
                            component.items = [];
                        }
                    });
            },

            dayFormat(date) {
                return moment.utc(date).local().format('ddd MMM D');
            },

            convertToShift(item) {
                this.selectedItem = item;
                this.convertModal = true;
            },

            markConverted(key, newId) {
                this.items.map(item => {
                    if (item.key === key) {
                        item.converted = true;
                        item.shift_id = newId;
                    }
                    return item;
                })
            }

        },
    }
</script>

<style>
    table {
        font-size: 14px;
    }
</style>