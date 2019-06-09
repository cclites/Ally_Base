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
                        <b-col lg="6">
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
                            <b-form-group label="Client Status">
                                <b-form-select v-model="filters.active">
                                    <option :value="null">All Clients</option>
                                    <option :value="true">Active Clients</option>
                                    <option :value="false">Inactive Clients</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>

                        <b-col lg="3">
                            <b-form-group label="Client Type">
                                <client-types-dropdown v-model="filters.client_type" @clientType="updateClientType"></client-types-dropdown>
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
                            <!-- MODAL TO SELECT COLUMNS -->
                            <report-column-picker prefix="client_directory_" v-bind:columns.sync="columns" />
                        </b-col>
                        <b-col sm="6" class="text-right">
                            <b-btn :href="downloadableUrl" variant="success"><i class="fa fa-file-excel-o"></i> Export to Excel</b-btn>
                            <b-btn @click="printTable()" variant="primary"><i class="fa fa-print"></i> Print</b-btn>
                        </b-col>
                    </b-row>

                    <ally-table id="table" :columns="fields" :items="items">
                        <template slot="active" scope="row">
                            {{ row.item.active ? 'Active' : 'Inactive' }}
                        </template>
                        <template slot="address" scope="row">
                            {{ addressFormat(row.item.address) }}
                        </template>
                        <template slot="created_at" scope="row">
                            {{ formatDate(row.item.user.created_at) }}
                        </template>
                        <template v-for="key in customFieldKeys" :slot="key" scope="row">
                            {{ getFieldValue(row.item.meta, key) }}
                        </template>
                    </ally-table>
                </b-card>
            </b-col>
        </b-row>
    </div>    
 </template>
 
 <script>
 import moment from 'moment';
 import FormatsListData from '../../../mixins/FormatsListData';
 import UserDirectory from '../../../mixins/UserDirectory';
 import FormatsDates from "../../../mixins/FormatsDates";

 export default {
     props: {
         clients: {
             required: true,
             type: Array,
         },
     },

     mixins: [FormatsListData, FormatsDates, UserDirectory],

    data() {
        return {
            directoryType: 'client',
            data: this.clients,
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
                client_type: {
                    key: 'client_type',
                    label: 'Client Type',
                    shouldShow: true,
                    formatter: val => this.uppercaseWords(val)
                },
                created_at: {
                    key: 'created_at',
                    label: 'Date Added',
                    shouldShow: true,
                    formatter: val => this.formatDate(val)
                },
            },
            filters:{
                client_type: '',
            }

        };
    },

     methods: {
         updateClientType(type){
             this.filters.client_type = type;
         },
     },

 }
 </script>
 