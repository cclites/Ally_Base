<template>
    <div>
        <b-card 
            header="Select Date Range"
            header-text-variant="white"
            header-bg-variant="info"
        >
            <b-row>
                <b-col lg="6">
                    <b-form inline @submit.prevent="loadReport()">
                        <date-picker
                            v-model="start_date"
                            placeholder="Start Date"
                            class="mt-1"
                        >
                        </date-picker> &nbsp;to&nbsp;
                        <date-picker
                            v-model="end_date"
                            placeholder="End Date"
                            class="mt-1"
                        >
                        </date-picker>&nbsp;
                        <b-button 
                            type="submit" 
                            variant="info"
                            class="mt-1"
                        >
                            Generate Report
                        </b-button>&nbsp;
                    </b-form>
                </b-col>

                <b-col lg="6" class="text-right">
                    <b-form-input v-model="filter" placeholder="Type to Search" />
                </b-col>
            </b-row>
        </b-card>
        
        <b-card>
            <loading-card v-show="loading"></loading-card>

            <div v-show="! loading" class="table-responsive">
                <b-table bordered striped hover show-empty
                    :items="items"
                    :fields="fields"
                    :sort-by.sync="sortBy"
                    :sort-desc.sync="sortDesc"
                    :per-page="perPage"
                    :current-page="currentPage"
                    :filter="filter"
                >
                    <template slot="actions" scope="row">
                        <b-btn size="sm" @click="openViewModal(row.item)">View</b-btn>
                    </template>
                </b-table>
            </div>

            <b-row>
                <b-col lg="6">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage"/>
                </b-col>
                <b-col lg="6" class="text-right">
                    Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
                </b-col>
            </b-row>
        
        </b-card>

        <b-modal id="viewModal" 
            title="Audit Entry Details" 
            v-model="viewModal" 
            size="lg" 
            ok-only 
            ok-title="Close" 
            ok-variant="secondary"
        >
            <h4>User Details</h4>
            <b-row>
                <b-col sm="4">Username</b-col><b-col>{{ entry.user? entry.user.username : '' }}</b-col>
            </b-row>
            <b-row>
                <b-col sm="4">IP</b-col><b-col>{{ entry.ip_address }}</b-col>
            </b-row>
            <b-row>
                <b-col sm="4">Date</b-col><b-col>{{ entry.created_at }}</b-col>
            </b-row>
            <b-row>
                <b-col sm="4">Useragent</b-col><b-col>{{ entry.user_agent}}</b-col>
            </b-row>
            <b-row>
                <b-col sm="4">URL</b-col><b-col>{{ entry.url }}</b-col>
            </b-row>

            <h4 class="mt-4">Modified Instance</h4>
            <b-row>
                <b-col sm="4">{{ entry.auditable_title }}</b-col><b-col>{{ entry.auditable_id }}</b-col>
            </b-row>

            <h4 class="mt-4">Modified Values</h4>
            <b-row>
                <b-col sm="4" class="font-weight-bold">Attribute</b-col>
                <b-col sm="4" class="font-weight-bold">Old Value</b-col>
                <b-col sm="4" class="font-weight-bold">New Value</b-col>
            </b-row>
            <b-row v-for="(item, key) in entry.diff" :key="key" class="my-4">
                <b-col>{{ key }}</b-col>
                <b-col>{{ item.old || '(none)' }}</b-col>
                <b-col>{{ item.new || '(none)' }}</b-col>
            </b-row>

        </b-modal>
    </div>
</template>

<script>
    export default {
        data: () => ({
            loading: false,
            viewModal: false,
            entry: {},

            // filters
            start_date: '',
            end_date: '',

            // table
            totalRows: 0,
            perPage: 50,
            currentPage: 1,
            sortBy: 'created_at',
            sortDesc: true,
            filter: '',
            items: [],
            fields: [
                {
                    key: 'created_at',
                    label: 'Date Modified',
                    sortable: true,
                },
                {
                    key: 'auditable_title',
                    label: 'Modified Type',
                    sortable: true,
                },
                {
                    key: 'id',
                    label: 'Modified ID',
                    sortable: true,
                },
                {
                    key: 'event',
                    sortable: true,
                },
                {
                    key: 'ip_address',
                    label: 'IP Address',
                    sortable: true,
                },
                {
                    key: 'actions',
                    sortable: false,
                },
            ]
        }),

        computed: {
        },

        methods: {
            loadReport() {
                this.loading = true;
                axios.get(`/admin/audit-log?start=${this.start_date}&end=${this.end_date}`)
                    .then( ({ data }) => {
                        this.items = data;
                        this.loading = false;
                    })
                    .catch(e => {
                        console.log(e);
                        this.loading = false;
                    });
            },

            openViewModal(entry) {
                this.viewModal = true;
                this.entry = entry;
            },
        },

        mounted() {
            this.start_date = moment().format('MM/DD/YYYY');
            this.end_date = moment().format('MM/DD/YYYY');
        },
    }
</script>
