<template>
    <div>
        <!-- FILTERS CARD -->
        <b-row>
            <b-col lg="12">
                <b-card
                    header="Select Filters"
                    header-text-variant="white"
                    header-bg-variant="info"
                >
                    <b-row>
                        <b-col sm="3">
                            <business-location-select v-model="filters.businesses" :allow-all="true" :hideable="false" class="f-1 mr-2"></business-location-select>
                        </b-col>
                        <b-col sm="3">
                            <b-form-select v-model="filters.active">
                                <option :value="null">All Clients</option>
                                <option :value="true">Active Clients</option>
                                <option :value="false">Inactive Clients</option>
                            </b-form-select>
                        </b-col>
                        <b-col sm="3">
                            <b-form-select v-model="filters.client_type" class="mb-2 mr-2" name="client_id">
                                <option v-for="item in clientTypes" :key="item.value" :value="item.value">{{ item.text }}</option>
                            </b-form-select>
                        </b-col>
                        <b-col sm="3">
                            <b-form-select name="status_alias_id" v-model=" filters.status_alias_id ">
                                <option value="">All Status Aliases</option>
                                <option v-for=" ( alias, i ) in statusAliases " :key=" i " :value=" alias.id ">{{ alias.name }}</option>
                            </b-form-select>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col md="3">
                            <b-button @click="loadTable()" variant="info" :disabled="busy">
                                <i class="fa fa-circle-o-notch fa-spin mr-1" v-if="busy"></i>
                                Generate Report
                            </b-button>
                        </b-col>
                    </b-row>
                </b-card>
            </b-col>
        </b-row>

        <!-- TABLE CARD -->
        <b-row>
            <b-col lg="12">
                <b-card>
                    <b-row class="mb-2">
                        <b-col sm="12" class="text-right">
                            <b-btn @click="exportExcel()" variant="success"><i class="fa fa-file-excel-o"></i> Export to Excel</b-btn>
                            <b-btn @click="printTable()" variant="primary"><i class="fa fa-print"></i> Print</b-btn>
                        </b-col>
                    </b-row>

                    <b-row>
                        <b-col lg="6">
                            <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
                        </b-col>
                        <b-col lg="6" class="text-right">
                            Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
                        </b-col>
                    </b-row>

                    <div id="table" class="table-responsive">
                        <b-table
                            bordered striped hover show-empty
                            :items="itemProvider"
                            :fields="fields"
                            :current-page.sync="currentPage"
                            :per-page="perPage"
                            :sort-by.sync="sortBy"
                            :sort-desc.sync="sortDesc"
                            :busy="loading"
                            ref="table"
                        >
                            <template slot="id" scope="row">
                                <a :href="`/business/clients/${row.item.id}`" target="_blank">{{ row.item.id }}</a>
                            </template>
                            <template slot="active" scope="row">
                                {{ row.item.active ? 'Active' : 'Inactive' }}
                            </template>
                        </b-table>
                    </div>

                    <b-row>
                        <b-col lg="6">
                            <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
                        </b-col>
                        <b-col lg="6" class="text-right">
                            Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
                        </b-col>
                    </b-row>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import BusinessLocationSelect from "./../../business/BusinessLocationSelect";
    import FormatsListData from '../../../mixins/FormatsListData';
    import FormatsDates from "../../../mixins/FormatsDates";
    import Constants from '../../../mixins/Constants';

    export default {
        props: {
            customFields: {
                type: Array,
                required: true,
            }
        },
        mixins: [FormatsListData, FormatsDates, Constants],
        components: {BusinessLocationSelect},

        data() {
            return {
                loading: false,
                busy: false,
                statusAliases: [],
                directoryType: 'client',
                filters: new Form({
                    businesses: '',
                    active: null,
                    client_type: '',
                    status_alias_id: '',
                    json: 1
                }),
                totalRows: 0,
                perPage: 50,
                currentPage: 1,
                sortBy: 'lastname',
                sortDesc: false,
                columns: {
                    id: { label: 'ID', sortable: true },
                    firstname: { label: 'First Name', sortable: true },
                    lastname: { label: 'Last Name', sortable: true },
                    username: { sortable: true },
                    date_of_birth: { sortable: true },
                    gender: { sortable: true },
                    email: { sortable: true },
                    active: { label: 'Client Status', sortable: true },
                    office_location: { sortable: false },
                    address: { sortable: false },
                    phone: { sortable: false },
                    client_type: { sortable: true },
                    status_alias: { sortable: false },
                    created_at: {
                        sortable: true,
                        formatter: val => this.formatDateFromUTC(val)
                    },
                    created_by: { sortable: false },
                    updated_at: {
                        label: 'Modified On',
                        sortable: true,
                        formatter: val => val ? this.formatDateTimeFromUTC(val) : '-'
                    },
                    updated_by: { label: 'Modified By', sortable: false },
                    services_coordinator: { sortable: false },
                    salesperson: { sortable: false },
                    inquiry_date: {
                        sortable: true,
                        formatter: val => val ? this.formatDateTimeFromUTC(val) : '-'
                    },
                    service_start_date: {
                        sortable: true,
                        formatter: val => val ? this.formatDateTimeFromUTC(val) : '-'
                    },
                    referral: { sortable: false },
                    ambulatory: { sortable: true },
                    caregiver_1099: { sortable: true },
                    agreement_status: { sortable: true },
                    hic: { sortable: true },
                    diagnosis: { sortable: true },
                },
            };
        },

        computed: {
            fields() {
                let cols = this.columns;
                this.customFields.forEach(x => {
                    cols[x.key] = {
                        key: x.key,
                        sortable: false,
                    }
                });
                return cols;
            },
        },

        methods: {
            loadTable() {
                this.$refs.table.refresh();
            },

            itemProvider(ctx) {
                this.loading = true;
                let sort = ctx.sortBy == null ? 'lastname' : ctx.sortBy;
                return this.filters.get(`/business/reports/client-directory?&page=${ctx.currentPage}&perpage=${ctx.perPage}&sort=${sort}&desc=${ctx.sortDesc}`)
                    .then( ({ data }) => {
                        this.totalRows = data.total;
                        return data.rows || [];
                    })
                    .catch(() => {
                        return [];
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            },

            async fetchStatusAliases() {
                axios.get(`/business/status-aliases`)
                    .then( ({ data }) => {
                        if (data && data.client) {
                            this.statusAliases = data.client;
                        } else {
                            this.statusAliases = [];
                        }
                    })
                    .catch(() => {});
            },

            exportExcel() {
                let sort = this.sortBy == null ? 'lastname' : this.sortBy;
                window.location = this.filters.toQueryString(`/business/reports/client-directory?export=1&sort=${sort}&desc=${this.sortDesc}`);
            },

            printTable() {
                $('#table').print();
            },
        },

        async mounted() {
            await this.fetchStatusAliases();
        }
    }
</script>