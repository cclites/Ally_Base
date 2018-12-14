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

 export default {
     props: {
         caregivers: {
             required: true,
             type: Array,
         },
     },

     mixins: [FormatsListData, UserDirectory],

    data() {
        return {
            directoryType: 'caregiver',
            data: this.caregivers,
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
 }
 </script>
 