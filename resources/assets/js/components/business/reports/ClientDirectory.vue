 <template>
    <div>
        <!-- FILTERS CARD -->
        <b-row>
            <b-col lg="12">
                <b-card
                    header="Select Date Range &amp; Filters"
                    header-text-variant="white"
                    header-bg-variant="info"
                >
                    <b-row>
                        <b-col lg="5">
                            <b-form-group label="Clients added between" class="form-inline">
                                <date-picker 
                                    v-model="filters.start_date"
                                    placeholder="Start Date"
                                />
                                &nbsp;and&nbsp;
                                <date-picker 
                                    v-model="filters.end_date"
                                    placeholder="End Date"
                                />
                            </b-form-group>
                        </b-col>

                        <b-col lg="3">
                            <b-form-group label="Client status">
                                <b-form-select v-model="filters.client_active">
                                    <option :value="null">All Clients</option>
                                    <option :value="true">Active Clients</option>
                                    <option :value="false">Inactive Clients</option>
                                </b-form-select>
                            </b-form-group>
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
                        <b-col sm="6">
                            <b-btn @click="columnsModal = true" variant="primary">Show or Hide Columns</b-btn>
                        </b-col>
                        <b-col sm="6" class="text-right">
                            <b-btn href="#" variant="success"><i class="fa fa-file-excel-o"></i> Export to Excel</b-btn>
                            <b-btn @click="printTable()" variant="primary"><i class="fa fa-print"></i> Print</b-btn>
                        </b-col>
                    </b-row>

                    <b-table
                        id="clients-table"
                        bordered
                        striped
                        hover
                        show-empty
                        :items="items"
                        :fields="fields"
                        :current-page="currentPage"
                        :per-page="perPage"
                         @filtered="onFiltered"
                    >
                        <template slot="active" scope="row">
                            {{ row.item.active ? 'Active' : 'Inactive' }}
                        </template>
                        <template slot="address" scope="row">
                            {{ addressFormat(row.item.address) }}
                        </template>
                        <template slot="created_at" scope="row">
                            {{ formatDate(row.item.user.created_at) }}
                        </template>
                    </b-table>
                    <b-row>
                        <b-col lg="6" >
                            <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
                        </b-col>
                        <b-col lg="6" class="text-right">
                            Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
                        </b-col>
                    </b-row>
                </b-card>
            </b-col>
        </b-row>

        <!-- MODAL TO SELECT COLUMNS -->
        <b-modal title="Show or Hide Columns" v-model="columnsModal">
            <b-container fluid>
                <b-row>
                    <div class="form-check row">
                        <div class="col-sm-auto" v-for="(field, key) in columns" :key="key">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" v-model="field.shouldShow" :value="true">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">{{ field.label }}</span>
                            </label>
                        </div>
                    </div>
                </b-row>
            </b-container>
            <div slot="modal-footer">
                <b-btn variant="default" @click="columnsModal = false">Close</b-btn>
            </div>
        </b-modal>
    </div>    
 </template>
 
 <script>
 import moment from 'moment';
 import FormatsListData from '../../../mixins/FormatsListData';

 export default {
     props: {
         clients: {
             required: true,
             type: Array,
         },
     },

     mixins: [FormatsListData],

    data() {
        return {
            totalRows: 0,
            perPage: 15,
            currentPage: 1,
            columnsModal: false,
            filters: {
                start_date: '',
                end_date: '',
                client_active: null,
            },
            columns: {
                firstname: {
                    key: 'firstname',
                    label: 'First name',
                    shouldShow: true,
                },
                lastname: {
                    key: 'lastname',
                    label: 'Last name',
                    shouldShow: true,
                },
                email: {
                    key: 'email',
                    label: 'Email',
                    shouldShow: true,
                    formatter: this.formatEmail,
                },
                active: {
                    key: 'active',
                    label: 'Client Status',
                    shouldShow: true,
                },
                address: {
                    key: 'address',
                    label: 'Address',
                    shouldShow: true,
                },
                created_at: {
                    key: 'created_at',
                    label: 'Date Added',
                    shouldShow: true,
                },
            },
        };
    },

    computed: {
        fields() {
            let fields = Object.keys(this.columns).filter(key => this.columns[key].shouldShow);
            fields = fields.map(col => ({
                    sortable: true,
                    ...this.columns[col],
            }));

            return fields;
        },

        items() {
            const {start_date, end_date, client_active} = this.filters;
            let items = this.clients;

            if(start_date && end_date) {
                items = items.filter(({user}) => moment(user.created_at).isBetween(start_date, end_date));
            }

            if(typeof client_active == 'boolean') {
                items = items.filter(client => client.active == client_active);
            }

            return items;
        }
    },

    methods: {
        formatDate(date) {
            return moment(date).format('MM-DD-YYYY');
        },

        onFiltered(filteredItems) {
            // Trigger pagination to update the number of buttons/pages due to filtering
            this.totalRows = filteredItems.length;
            this.currentPage = 1;
        },

        printTable() {
            $('#clients-table').print();
        }
    },
 }
 </script>
 