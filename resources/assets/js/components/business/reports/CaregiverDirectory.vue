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
                        <b-col sm="4">
                            <b-form-group label="Caregiver status">
                                <b-form-select v-model=" form.active ">
                                    <option :value=" null ">All Caregivers</option>
                                    <option :value=" true ">Active Caregivers</option>
                                    <option :value=" false ">Inactive Caregivers</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                        <b-col sm="4">

                            <b-form-group label="Status Alias">
                                <b-form-select name="status_alias_id" v-model=" form.status_alias_id ">
                                    <option value="">All Aliases</option>
                                    <option v-for=" ( alias, i ) in statusAliases " :key=" i " :value=" alias.id ">{{ alias.name }}</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col md="3">
                            <b-button @click=" fetch() " variant="info" :disabled=" busy " class="mr-1 mt-1">
                                <i class="fa fa-circle-o-notch fa-spin mr-1" v-if=" busy "></i>
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
                        <!-- <b-col sm="6">
                            <report-column-picker prefix="caregiver_directory_" v-bind:columns.sync="columns" />
                        </b-col> -->
                        <b-col sm="12" class="text-right">
                            <b-btn @click=" exportExcel() " variant="success"><i class="fa fa-file-excel-o"></i> Export to Excel</b-btn>
                            <b-btn @click="printTable()" variant="primary"><i class="fa fa-print"></i> Print</b-btn>
                        </b-col>
                    </b-row>

                    <b-row>
                        <b-col lg="6">
                            <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="form.current_page"/>
                        </b-col>
                        <b-col lg="6" class="d-flex justify-content-end align-content-center">
                            <p style="height:25px; margin: auto 0;">{{ paginationStats }}</p>
                        </b-col>
                    </b-row>

                    <div id="table" class="table-responsive">
                        <b-table
                            bordered striped hover show-empty
                            :items=" items "
                            :fields=" fields "
                            :per-page=" 100 "
                        >
                            <template v-for=" field in fields " :slot=" field.key || field " scope="data" >
                                <slot v-bind="data" :name="field.key || field"> {{ renderCell( data.item, field ) }}</slot>
                            </template>
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
                            <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="form.current_page"/>
                        </b-col>
                        <b-col lg="6" class="text-right">
                            <p style="height:25px; margin: auto 0;">{{ paginationStats }}</p>
                        </b-col>
                    </b-row>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import FormatsListData from '../../../mixins/FormatsListData';
    import FormatsDates from "../../../mixins/FormatsDates";

    export default {
        mixins: [FormatsListData, FormatsDates],
        props: {
            customFields: {
                type: Array,
                required: true,
            },
        },

        data() {
            return {
                form: new Form({
                    active: null,
                    current_page: 1,
                    status_alias_id: '',
                    json: 1
                }),
                busy: false,
                statusAliases: [],
                totalRows: 0,
                perPage: 100,
                items: [],
                columns: {
                    id: {
                        label: 'ID',
                        sortable: true,
                    },
                    firstname: {
                        label: 'First name',
                        sortable: true,
                    },
                    lastname: {
                        label: 'Last name',
                        sortable: true,
                    },
                    username: {
                        label: 'User Name',
                        sortable: true,
                    },
                    title: {
                        sortable: true,
                    },
                    date_of_birth: { sortable: true, formatter: x => x ? this.formatDate(x) : '-' },
                    certification: {
                        sortable: true,
                    },
                    gender: {
                        sortable: true,
                    },
                    orientation_date: {
                        sortable: true,
                        formatter: val => val ? this.formatDateTimeFromUTC(val) : '-'
                    },
                    smoking_okay: {
                        sortable: true,
                    },
                    pets_dogs_okay: {
                        sortable: true,
                    },
                    pets_cats_okay: {
                        sortable: true,
                    },
                    pets_birds_okay: {
                        sortable: true,
                    },
                    ethnicity: {
                        sortable: true,
                    },
                    application_date: {
                        sortable: true,
                        formatter: val => val ? this.formatDateTimeFromUTC(val) : '-'
                    },
                    status_alias: {
                        sortable: false,
                    },
                    medicaid_id: {
                        label: 'Medicaid ID',
                        sortable: true
                    },
                    email: {
                        sortable: true,
                    },
                    active: {
                        label: 'Caregiver Status',
                        sortable: true,
                    },
                    address: {
                        sortable: false,
                    },
                    phone: {
                        sortable: false,
                    },
                    emergency_contact: {
                        label: 'Emergency Contact',
                        sortable: false,
                    },
                    created_at: {
                        label: 'Date Added',
                        sortable: true,
                        formatter: val => val ? this.formatDateTimeFromUTC(val) : '-'
                    },
                    referral: {
                        sortable: false,
                    },
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

            paginationStats() {
                if (this.busy) {
                    return `Fetching page ${this.form.current_page}`;
                }

                const offset = this.perPage * (this.form.current_page - 1);
                const current_last = offset + this.items.length;
                return `Showing ${offset} - ${current_last} of ${this.totalRows} results`;
            }
        },

        methods: {
            async fetchStatusAliases() {
                let response = await axios.get('/business/status-aliases');
                if (response.data && response.data.caregiver) {
                    this.statusAliases = response.data.caregiver.map(alias => {
                        return {'name': alias.name, 'id': alias.id}
                    });
                }
            },

            fetch() {
                this.busy = true;
                this.form.get('/business/reports/caregiver-directory')
                    .then(({data}) => {
                        this.items = data.rows;
                        this.totalRows = data.total;
                    })
                    .catch(() => {})
                    .finally(() => {
                        this.busy = false;
                    })
            },

            renderCell(row, field) {
                const value = row[field.key || field];
                return field.formatter ? field.formatter(value) : value;
            },

            exportExcel() {
                window.location = this.form.toQueryString('/business/reports/caregiver-directory?export=1');
            },

            printTable() {
                $('#table').print();
            },
        },

        created() {
            this.fetch();
        },

        async mounted() {
            await this.fetchStatusAliases();
        },

        watch: {
            'form.current_page': function (val, oldVal) {
                this.fetch();
            }
        },
    }
</script>
 