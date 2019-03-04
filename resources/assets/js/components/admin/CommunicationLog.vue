<template>
    <b-card>
        <b-row>
            <b-col lg="12">
                <b-card header="Select Date Range"
                    header-text-variant="white"
                    header-bg-variant="info"
                >
                    <b-form inline @submit.prevent="fetch()">
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
                        <label for="channel" class="mx-2">Type</label>
                        <b-select v-model="channel" id="channel">
                            <option value="">All</option>
                            <option value="mail">Email</option>
                            <option value="sms">SMS</option>
                        </b-select>
                        <b-btn variant="info" type="submit" class="ml-3">Generate Report</b-btn>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>
        <b-row>
            <b-col lg="12" class="text-right">
                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
        </b-row>

        <loading-card v-if="loading"></loading-card>

        <div v-else class="table-responsive">
            <b-table bordered striped hover show-empty
                :items="items"
                :fields="fields"
                :sort-by.sync="sortBy"
                :sort-desc.sync="sortDesc"
                :filter="filter"
            >
                <template slot="to" scope="row">
                    {{ row.item.to }}
                </template>
            </b-table>
        </div>
    </b-card>
</template>

<script>
    import FormatsDates from "../../mixins/FormatsDates";
    export default {
        mixins: [ FormatsDates ],

        props: {
        },

        data() {
            return {
                loading: false,
                sortBy: 'sent_at',
                sortDesc: true,
                filter: null,
                start_date: moment().format('MM/DD/YYYY'),
                end_date: moment().format('MM/DD/YYYY'),
                channel: '',
                items: [],
                fields: [
                    {
                        key: 'channel',
                        label: 'Type',
                        sortable: true,
                        formatter: (x) => x == 'sms' ? 'SMS' : x == 'mail' ? 'Email' : '-',
                    },
                    {
                        key: 'from',
                        sortable: true,
                    },
                    {
                        key: 'to',
                        sortable: true,
                    },
                    {
                        key: 'subject',
                        label: 'Subject',
                        sortable: true,
                        formatter: (x) => x ? x : '-',
                    },
                    {
                        key: 'preview',
                        label: 'Message',
                        sortable: true,
                        formatter: (x) => x.length === 100 ? x+'...' : x,
                    },
                    {
                        key: 'sent_at',
                        label: 'Date',
                        sortable: true,
                        formatter: (val) => this.formatDateTimeFromUTC(val),
                    },
                    {
                        key: 'action',
                        label: '',
                        sortable: false,
                    },
                ],
            };
        },

        computed: {
        },

        methods: {
            async fetch() {
                this.loading = true;
                let url = `/admin/communication-log?json=1&channel=${this.channel}&start_date=${this.start_date}&end_date=${this.end_date}`;
                axios.get(url)
                    .then( ({ data }) => {
                        this.items = data.data;
                    })
                    .catch(e => {
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            },
        },

        mounted() {
        },
    }
</script>

<style>
</style>
