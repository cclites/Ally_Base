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
                            <b-form-group label="Caregivers added between" class="form-inline">
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
                            <b-form-group label="Caregiver status">
                                <b-form-select v-model="filters.active">
                                    <option :value="null">All Caregivers</option>
                                    <option :value="true">Active Caregivers</option>
                                    <option :value="false">Inactive Caregivers</option>
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
                            <!-- MODAL TO SELECT COLUMNS -->
                            <report-column-picker prefix="caregiver_directory_" v-bind:columns.sync="columns" />
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
    </div>    
 </template>
 
 <script>
 import moment from 'moment';
 import FormatsListData from '../../../mixins/FormatsListData';
 import UserDirectory from '../../../mixins/UserDirectory';

 export default {
     props: {
         caregivers: {
             required: true,
             type: Array,
         },
         customFields: {
             type: Array,
             required: true,
         },
     },

     mixins: [FormatsListData, UserDirectory],

     created() {
         const obj = {};
         const customKeys = [];
         this.customFields.forEach(({key, label}) => {
             customKeys.push(key);
             obj[key] = {
                sortable: true,
                shouldShow: true,
                key,
                label,
             };
         });

        this.customFieldKeys = customKeys;
         this.columns = {
             ...this.columns,
             ...obj,
         };
     },

    data() {
        return {
            directoryType: 'caregiver',
            data: this.caregivers,
            customFieldKeys: [],
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
                    label: 'Caregiver Status',
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

    methods: {
        getFieldValue(meta, key) {
            const metaField = meta.find(fieldValue => fieldValue.key == key);
            const {required, default_value, options} = this.customFields.find(definition => definition.key == key);
            const isDropdown = options.length > 0;

            if(!metaField) {
                return isDropdown && required ? this.getDropdownLabel(options, default_value) : default_value;
            }

            return isDropdown ? this.getDropdownLabel(options, metaField.value) : metaField.value;
        },

        getDropdownLabel(options, key) {
            console.log(options, key)
            return options.find(option => option.value == key).label;
        }
    },
 }
 </script>
 