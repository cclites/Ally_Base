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
                            <b-form-select v-model=" filters.active ">
                                <option :value="null">All Caregivers</option>
                                <option :value="true">Active Caregivers</option>
                                <option :value="false">Inactive Caregivers</option>
                            </b-form-select>
                        </b-col>
                        <b-col sm="3">
                            <b-form-select name="status_alias_id" v-model=" filters.status_alias_id ">
                                <option value="">All Aliases</option>
                                <option v-for=" ( alias, i ) in statusAliases " :key=" i " :value=" alias.id ">{{ alias.name }}</option>
                            </b-form-select>
                        </b-col>
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
                            <b-btn @click=" exportExcel() " variant="success"><i class="fa fa-file-excel-o"></i> Export to Excel</b-btn>
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
                                <a :href="`/business/caregivers/${row.item.id}`" target="_blank">{{ row.item.id }}</a>
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

    export default {
        mixins: [FormatsListData, FormatsDates],
        components: {BusinessLocationSelect},
        props: {
            customFields: {
                type: Array,
                required: true,
            },
        },

        data() {
            return {
                filters: new Form({
                    businesses: '',
                    active: null,
                    status_alias_id: '',
                    json: 1
                }),
                loading: false,
                busy: false,
                statusAliases: [],
                totalRows: 0,
                perPage: 50,
                currentPage: 1,
                sortBy: 'lastname',
                sortDesc: false,
                columns: {
                    id: { label: 'ID', sortable: true, },
                    firstname: { label: 'First name', sortable: true, },
                    lastname: { label: 'Last name', sortable: true, },
                    username: { label: 'User Name', sortable: true, },
                    email: { sortable: true, },
                    title: { sortable: true, },
                    date_of_birth: { sortable: true, formatter: x => x ? this.formatDate(x) : '-' },
                    certification: { sortable: true, },
                    gender: { sortable: true, },
                    active: { label: 'Caregiver Status', sortable: true, },
                    status_alias: { sortable: false, },
                    office_location: { label: 'Office Locations', sortable: false },
                    created_at: {
                        label: 'Date Added',
                        sortable: true,
                        formatter: val => val ? this.formatDateTimeFromUTC(val) : '-'
                    },
                    address: { sortable: false, },
                    phone: { sortable: false, },
                    notification_phone: { sortable: true },
                    application_date: {
                        sortable: true,
                        formatter: val => val ? this.formatDateTimeFromUTC(val) : '-'
                    },
                    orientation_date: {
                        sortable: true,
                        formatter: val => val ? this.formatDateTimeFromUTC(val) : '-'
                    },
                    smoking_okay: { sortable: true, },
                    pets_dogs_okay: { sortable: true, },
                    pets_cats_okay: { sortable: true, },
                    pets_birds_okay: { sortable: true, },
                    ethnicity: { sortable: true, },
                    medicaid_id: { label: 'Medicaid ID', sortable: true },
                    emergency_contact: { label: 'Emergency Contact', sortable: false, },
                    referral: { sortable: false, },
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
                return this.filters.get(`/business/reports/caregiver-directory?&page=${ctx.currentPage}&perpage=${ctx.perPage}&sort=${sort}&desc=${ctx.sortDesc}`)
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
                        if (data && data.caregiver) {
                            this.statusAliases = data.caregiver;
                        } else {
                            this.statusAliases = [];
                        }
                    })
                    .catch(() => {});
            },

            exportExcel() {
                let sort = this.sortBy == null ? 'lastname' : this.sortBy;
                window.location = this.filters.toQueryString(`/business/reports/caregiver-directory?export=1&sort=${sort}&desc=${this.sortDesc}`);
            },

            printTable() {
                $('#table').print();
            },
        },

        async mounted() {
            await this.fetchStatusAliases();
        },
    }
</script>
 