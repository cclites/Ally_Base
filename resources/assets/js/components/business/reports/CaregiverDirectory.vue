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
 import FormatsDates from "../../../mixins/FormatsDates";

 export default {
     props: {
         caregivers: {
             required: true,
             type: Array,
         },
     },

     mixins: [FormatsListData, FormatsDates, UserDirectory],

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
                username: {
                    key: 'username',
                    label: 'User Name',
                    shouldShow: true,
                },
                title: {
                    key: 'title',
                    label: 'Title',
                    shouldShow: true,
                },
                certification: {
                    key: 'certification',
                    label: 'Certification',
                    shouldShow: true,
                },
                gender: {
                    key: 'gender',
                    label: 'Gender',
                    shouldShow: true,
                },
                orientation_date: {
                    key: 'orientation_date',
                    label: 'Orientation Date',
                    shouldShow: true,
                    formatter: val => this.formatDate(val)
                },
                smoking_okay: {
                    key: 'smoking_okay',
                    label: 'Smoking Okay',
                    shouldShow: true,
                },
                ethnicity: {
                    key: 'ethnicity',
                    label: 'Ethnicity',
                    shouldShow: true,
                },
                application_date: {
                    key: 'application_date',
                    label: 'Application Date',
                    shouldShow: true,
                    formatter: val => this.formatDate(val)
                },
                medicaid_id: {
                    key: 'medicaid_id',
                    label: 'Medicaid ID',
                    shouldShow: true
                },
                email: {
                    key: 'email',
                    label: 'Email',
                    shouldShow: true,
                    formatter: this.formatEmail,
                },
                notification_phone: {
                    key: 'phone',
                    label: 'Phone',
                    shouldShow: true,
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
                phone: {
                    key: 'phone',
                    label: 'Phone',
                    shouldShow: true,
                },
                emergency_contact: {
                    key: 'emergency_contact',
                    label: 'Emergency Contact',
                    shouldShow: true,
                },
                created_at: {
                    key: 'created_at',
                    label: 'Date Added',
                    shouldShow: true,
                    formatter: val => this.formatDate(val)
                },
                referral: {
                    key: 'referral',
                    label: 'Referral',
                    shouldShow: true,
                },
            },
        };
    },
 }
 </script>
 