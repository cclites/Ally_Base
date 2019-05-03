<template>
    <b-container fluid>
        <b-row>
            <b-col>
                <b-card header="Disaster Code Plan Report"
                    header-text-variant="white"
                    header-bg-variant="info"
                >
                    <b-row>
                        <b-col md="2">
                            <b-form-group label="Client" label-for="client_id">
                                <b-select name="client_id" id="client_id" v-model="form.client_id">
                                    <option value="">All Clients</option>
                                    <option v-for="item in clientList" :key="item.id" :value="item.id">{{ item.name }}</option>
                                </b-select>
                            </b-form-group>
                        </b-col>
                        <b-col md="2">
                            <b-form-group label="Client Status" label-for="client_status">
                                <b-select name="client_status" id="client_status" v-model="form.client_status">
                                    <option value="">Any</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </b-select>
                            </b-form-group>
                        </b-col>
                        <b-col md="2">
                            <b-form-group label="Disaster Code Plan" label-for="disaster_code">
                                <b-select name="disaster_code" id="disaster_code" v-model="form.disaster_code">
                                    <option value="">All Code Plans</option>
                                    <option v-for="item in disasterCodes" :key="item" :value="item">{{ item }}</option>
                                </b-select>
                            </b-form-group>
                        </b-col>
                        <b-col md="2">
                            <b-form-group label="Zipcode" label-for="zipcode">
                                <b-form-input
                                    v-model="form.zipcode"
                                    id="zipcode"
                                    name="zipcode"
                                    type="text"
                                ></b-form-input>
                            </b-form-group>
                        </b-col>
                        <b-col md="2">
                            <business-location-form-group v-model="form.business_id"
                                class="mb-2 mr-2"
                                :allow-all="true"
                                :form="form"
                                field="business_id" />
                        </b-col>
                        <b-col md="2">

                            <b-form-group label="&nbsp;" label-for="">
                                <b-button @click="fetch()" variant="info" :disabled="busy || form.output_format == ''" class="mr-2 mb-2">
                                    <i class="fa fa-circle-o-notch fa-spin mr-1" v-if="busy"></i>
                                    Generate Report
                                </b-button>
                            </b-form-group>
                        </b-col>
                    </b-row>

                    <hr />

                    <div class="d-flex mb-2">
                        <div class="ml-auto">
                            <b-btn @click="columnsModal = true" variant="secondary">Show or Hide Columns</b-btn>
                            <b-btn @click="printTable()" variant="primary"><i class="fa fa-print"></i> Print</b-btn>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <b-table bordered striped hover show-empty
                            class="printable-table"
                            :busy="busy"
                            :items="items"
                            :fields="fields"
                            :current-page="currentPage"
                            :per-page="perPage"
                            :sort-by.sync="sortBy"
                            :sort-desc.sync="sortDesc">
                            <template slot="name" scope="row">
                                <a :href="`/business/clients/${row.item.id}`">{{ row.item.name }}</a>
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
            </b-col>
        </b-row>

        <!-- Filter columns modal -->
        <filter-columns-modal v-model="columnsModal"
                              :available-fields="availableFields"
                              :fields.sync="filteredFields"
        />

    </b-container>
</template>

<script>
    import BusinessLocationFormGroup from '../../../components/business/BusinessLocationFormGroup';
    import LocalStorage from "../../../mixins/LocalStorage";
    import FilterColumnsModal from "../../modals/FilterColumnsModal";

    export default {
        components: { BusinessLocationFormGroup, FilterColumnsModal },
        mixins: [ LocalStorage ],

        props: {
            clients: {
                type: [Object, Array],
                required: true,
                default: () => { return []; },
            },
        },

        data() {
            return {
                form: new Form({
                    client_id: '',
                    client_status: '',
                    disaster_code: '',
                    zipcode: '',
                    business_id: '',
                    json: 1,
                }),
                busy: false,
                totalRows: 0,
                perPage: 50,
                currentPage: 1,
                sortBy: 'name',
                sortDesc: false,
                items: [],
                filteredFields: [],
                columnsModal: false,
                localStoragePrefix: 'disaster_plan_report_',
            }
        },

        computed: {
            disasterCodes() {
                return ['1A', '1B', '1C', '1D', '1E', '1H', '1S', '2A', '2B', '2C', '2D', '2E', '2H', '2S', '3A', '3B', '3C', '3D', '3E', '3H', '3S', '4A', '4B', '4C', '4D', '4E', '4H', '4S'];
            },

            clientList() {
                if (this.form.business_id) {
                    return this.clients.filter(x => x.business_id == this.form.business_id);
                }

                return this.clients;
            },

            availableFields() {
                return [
                    'Office Location',
                    'Client',
                    'Client Status',
                    'Disaster Code Plan',
                    'Disaster Planning Description',
                    'Client Address',
                    'City',
                    'Zipcode',
                    'Client Phone 1',
                    'Client Phone 2',
                    'Emergency Contact',
                    'Emergency Contact Phone',
                ];
            },

            fields() {
                let fields = [];
                for (let field of this.availableFields) {
                    if (this.filteredFields.indexOf(field) !== -1) {
                        fields.push({
                            'key': field,
                            'sortable': true,
                        });
                    }
                }
                return fields;
            },
        },

        methods: {
            fetch() {
                this.busy = true;
                this.form.get('/business/reports/disaster-plan-report')
                    .then( ({ data }) => {
                        this.items = data;
                        this.totalRows = this.items.length;
                    })
                    .catch(() => {})
                    .finally(() => {
                        this.busy = false;
                    })
            },

            printTable() {
                $(".printable-table").print();
            },

            setInitialFields() {
                let fields = this.getLocalStorage('fields');
                if (fields && fields[0] && typeof(fields[0]) !== 'object') {
                    if (fields[0] && typeof(fields[0]) !== 'object') {
                        this.filteredFields = fields;
                        return;
                    }
                }
                this.filteredFields = this.availableFields.slice();
            },
        },

        mounted() {
            this.setInitialFields();
        },

        watch: {
            filteredFields(newValue) {
                this.setLocalStorage('fields', newValue);
            }
        },
    }
</script>
