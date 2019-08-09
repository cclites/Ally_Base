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

                        <!-- <b-col md="6" lg="3">

                            <b-form-group label="Added From">

                                <date-picker
                                    name="start_date"
                                    v-model=" form.start_date "
                                    placeholder="Start Date"
                                    :disabled=" busy "
                                />
                            </b-form-group>
                        </b-col>
                        <b-col md="6" lg="3">

                            <b-form-group label="Added To">

                                <date-picker
                                    name="end_date"
                                    v-model=" form.end_date "
                                    placeholder="End Date"
                                    :disabled=" busy "
                                />
                            </b-form-group>
                        </b-col> -->
                        <b-col sm="6">

                            <b-form-group label="Caregiver status">

                                <b-form-select v-model=" form.active ">

                                    <option :value=" null ">All Caregivers</option>
                                    <option :value=" true ">Active Caregivers</option>
                                    <option :value=" false ">Inactive Caregivers</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                        <b-col sm="6">

                            <b-form-group label="Status Alias">

                                <b-form-select>

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

                    <b-row>

                        <b-col lg="6">

                            <b-pagination :total-rows=" totalRows " :per-page=" perPage " v-model=" form.current_page "/>
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
                        </b-table>
                    </div>

                    <b-row>

                        <b-col lg="6">

                            <b-pagination :total-rows=" totalRows " :per-page=" perPage " v-model=" form.current_page "/>
                        </b-col>
                        <b-col lg="6" class="text-right">

                            <p style="height:25px; margin: auto 0;">{{ paginationStats }}</p>
                        </b-col>
                    </b-row>

                    <!-- <ally-table id="table" :columns=" fields " :items=" items " :perPage=" 100 ">

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
                    </ally-table> -->
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>

    // hiding this for now until better implemented server-side
    // :sort-by.sync=" sort "
    // @filtered=" onFiltered "

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

                    active       : null,
                    // start_date : moment().subtract( 6, 'days' ).format( 'MM/DD/YYYY' ),
                    // end_date   : moment().format( 'MM/DD/YYYY' ),
                    current_page : 1,
                    json         : 1
                }),
                totalRows       : 0,
                perPage         : 100,
                customFieldKeys : [],
                items           : [],
                columns         : {
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

                let fields = Object.keys( this.columns ).filter( key => this.columns[ key ].shouldShow );
                fields = fields.map( col => ({

                    sortable : true,
                    ...this.columns[ col ],
                }));

                return fields;
            },
            paginationStats(){

                if( this.busy ) return `Fetching page ${this.form.current_page}`;

                const offset = this.perPage * ( this.form.current_page - 1 );
                const current_last = offset + this.items.length;
                return `Showing ${offset} - ${current_last} of ${this.totalRows} results`;
            }
        },
        watch: {

            'form.current_page' : function( val, oldVal ){

                this.fetch();
            }
        },
        methods: {

            fetch() {

                this.busy = true;
                this.form.get( '/business/reports/caregiver-directory' )
                    .then( ({ data }) => {

                        console.log( 'data retreived: ', data );
                        this.items     = data.rows;
                        this.totalRows = data.total;
                    })
                    .catch( err => {

                        console.error( err );
                    })
                    .finally(() => {

                        this.busy = false;
                    })
            },
            renderCell( row, field ) {

                const value = row[ field.key || field ];
                return field.formatter ? field.formatter( value ) : value;
            },
            exportExcel(){

                window.location = this.form.toQueryString( '/business/reports/caregiver-directory?export=1' );
            },
            printTable() {

                $( '#table' ).print();
            },

            getFieldValue( meta, key ) {

                const metaField = meta.find(fieldValue => fieldValue.key == key);
                const {options, default: fieldDefault} = this.customFields.find(definition => definition.key == key);
                const isDropdown = options.length > 0;

                if( !metaField ) {

                    return fieldDefault;
                }

                return isDropdown ? this.getDropdownLabel( options, metaField.value ) : metaField.value;
            },

            getDropdownLabel( options, key ) {

                let option = options.find( option => option.value == key );
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
 