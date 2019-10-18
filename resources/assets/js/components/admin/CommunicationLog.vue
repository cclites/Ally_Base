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
                <template slot="actions" scope="row">
                    <b-btn size="sm" title="view" @click="view(row.item)"><i class="fa fa-eye"></i></b-btn>
                </template>
            </b-table>
        </div>

        <b-modal :title="`Communication Log #${current.id}`" v-model="showModal" size="lg">
            <loading-card v-if="loadingCurrent"></loading-card>
            <b-container fluid v-else>
                <b-row class="mb-2">
                    <b-col lg="6"><strong>To: </strong>{{ current.to }}</b-col>
                    <b-col lg="6"><strong>From: </strong>{{ current.from }}</b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col lg="6"><strong>Sent At: </strong>{{ formatDateTimeFromUTC(current.sent_at) }}</b-col>
                    <b-col lg="6"><strong>Type: </strong>{{ formatChannel(current.channel) }}</b-col>
                </b-row>
                <b-row v-if="current.subject" class="mb-2">
                    <b-col lg="12"><strong>Subject: </strong>{{ current.subject }}</b-col>
                </b-row>
                <b-row>
                    <b-col lg="12">
                        <div v-html="current.body"></div>
                    </b-col>
                </b-row>
                <b-row class="mt-4">
                    <b-col lg="12">
                        <h3 class="mb-0">Error</h3>
                        <div v-html="current.error || 'none' " :class=" ( current.error ? 'text-danger' : '' ) "></div>
                    </b-col>
                </b-row>
            </b-container>
            <div slot="modal-footer" scope="row">
                <b-btn variant="secondary" @click="showModal = false">Close</b-btn>
            </div>
        </b-modal>
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
                current: {},
                loadingCurrent: false,
                showModal: false,
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
                        formatter: (x) => this.formatChannel(x),
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
                        key: 'error',
                        label: 'Errors',
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        label: ' ',
                        sortable: false,
                    },
                ],
            };
        },

        computed: {
        },

        methods: {
            formatChannel(channel) {
                return channel == 'sms' ? 'SMS' : channel == 'mail' ? 'Email' : '-';
            },

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

            view(item) {
                this.showModal = true;
                this.loadingCurrent = true;
                axios.get(`/admin/communication-log/${item.id}`)
                    .then( ({ data }) => {
                        this.current = data;
                    })
                    .catch(e => {
                    })
                    .finally(() => {
                        this.loadingCurrent = false;
                    });
            },
        },

        mounted() {
        },
    }
</script>

<style>
</style>
