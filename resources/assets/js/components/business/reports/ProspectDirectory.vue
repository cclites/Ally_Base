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
                            <b-form-group label="Prospects added between" class="form-inline">
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
                            <b-btn :href="downloadableUrl" variant="success"><i class="fa fa-file-excel-o"></i> Export to Excel</b-btn>
                            <b-btn @click="printTable()" variant="primary"><i class="fa fa-print"></i> Print</b-btn>
                        </b-col>
                    </b-row>

                    <b-table
                        id="table"
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
                        <template slot="address" scope="row">
                            {{ addressFormat(row.item) }}
                        </template>
                        <template slot="created_at" scope="row">
                            {{ formatDate(row.item.created_at) }}
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
 import UserDirectory from '../../../mixins/UserDirectory';

 export default {
     props: {
         prospects: {
             required: true,
             type: Array,
         },
     },

     mixins: [FormatsListData, UserDirectory],

    data() {
        return {
            directoryType: 'prospect',
            data: this.prospects,
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
        items() {
            const {start_date, end_date} = this.filters;
            let items = this.data;

            if(start_date && end_date) {
                items = items.filter(({created_at}) => moment(created_at).isBetween(start_date, end_date));
            }

            return items;
        },
    },
 }
 </script>
 