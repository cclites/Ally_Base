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

                        <b-col lg="3">

                            <b-form-group label="Caregiver status">

                                <b-form-select v-model=" form.active ">

                                    <option :value=" null ">All Caregivers</option>
                                    <option :value=" true ">Active Caregivers</option>
                                    <option :value=" false ">Inactive Caregivers</option>
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

                    <ally-table id="table" :columns=" fields " :items=" items " :perPage=" 100 ">

                        <template slot="active" scope="row">

                            {{ row.item.active ? 'Active' : 'Inactive' }}
                        </template>
                        <template slot="address" scope="row">

                            {{ addressFormat( row.item.address ) }}
                        </template>
                        <template slot="created_at" scope="row">

                            {{ formatDate( row.item.user.created_at ) }}
                        </template>
                        <template v-for="key in customFieldKeys" :slot="key" scope="row">

                            {{ getFieldValue( row.item.meta, key ) }}
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
    // import UserDirectory from '../../../mixins/UserDirectory';
    import FormatsDates from "../../../mixins/FormatsDates";

    export default {

        mixins: [ FormatsListData, FormatsDates ],
        props: {

            customFields: {

                type     : Array,
                required : true,
            },
        },
        data() {

            return {

                busy          : false,
                form: new Form({

                    active : null,
                    json   : 1
                }),
                customFieldKeys: [],
                filters: {

                    start_date: '',
                    end_date: '',
                    active: null,
                    client_type: null,
                },
                directoryType : 'caregiver',
                items         : [],
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
        computed: {

            fields() {

                let fields = Object.keys(this.columns).filter(key => this.columns[key].shouldShow);
                fields = fields.map(col => ({
                        sortable: true,
                        ...this.columns[col],
                }));

                return fields;
            }
        },
        methods: {

            fetch() {

                this.busy = true;
                this.form.get( '/business/reports/caregiver-directory' )
                    .then( ({ data }) => {

                        this.items     = data;
                        this.totalRows = this.items.length;

                        console.log( this.items );
                    })
                    .catch( err => {

                        console.error( err );
                    })
                    .finally(() => {

                        this.busy = false;
                    })
            },
            exportExcel(){

                window.location = this.form.toQueryString( '/business/reports/caregiver-directory?export=1' );
            },
            printTable() {
                $('#table').print();
            },

            getFieldValue(meta, key) {
                const metaField = meta.find(fieldValue => fieldValue.key == key);
                const {options, default: fieldDefault} = this.customFields.find(definition => definition.key == key);
                const isDropdown = options.length > 0;

                if(!metaField) {
                    return fieldDefault;
                }

                return isDropdown ? this.getDropdownLabel(options, metaField.value) : metaField.value;
            },

            getDropdownLabel(options, key) {
                let option = options.find(option => option.value == key);
                return option ? option.label : '-';
            }
        },
        created(){

            this.fetch();

            const obj = {};
            const customKeys = [];
            this.customFields.forEach(({ key, label }) => {

                customKeys.push( key );
                obj[ key ] = {

                    sortable   : true,
                    shouldShow : true,
                    key,
                    label,
                };
            });

            this.customFieldKeys = customKeys;

            this.columns = {

                ...this.columns,
                ...obj,
            };
        }
    }
</script>
 