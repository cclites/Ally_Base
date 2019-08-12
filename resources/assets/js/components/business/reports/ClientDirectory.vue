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

                        <!-- <b-col lg="6">

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
                        </b-col> -->

                        <b-col sm="4">

                            <b-form-group label="Client Status">

                                <b-form-select v-model=" form.active ">

                                    <option :value="null">All Clients</option>
                                    <option :value="true">Active Clients</option>
                                    <option :value="false">Inactive Clients</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>

                        <b-col sm="4">

                            <b-form-group label="Client Type">

                                <b-form-select v-model=" form.client_type " class="mb-2 mr-2" name="client_id">

                                    <option v-for=" item in clientTypes " :key=" item.value " :value=" item.value ">{{ item.text }}</option>
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

                            <report-column-picker prefix="client_directory_" v-bind:columns.sync="columns" />
                        </b-col> -->
                        <b-col sm="12" class="text-right">

                            <b-btn @click=" exportExcel() " variant="success"><i class="fa fa-file-excel-o"></i> Export to Excel</b-btn>
                            <b-btn @click=" printTable()" variant="primary"><i class="fa fa-print"></i> Print</b-btn>
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

                                <slot v-bind=" data " :name="field.key || field"> {{ renderCell( data.item, field ) }}</slot>
                            </template>
                            <template slot="active" scope="row">

                                {{ row.item.active ? 'Active' : 'Inactive' }}
                            </template>
                            <template slot="address" scope="row">

                                {{ addressFormat( row.item.address ) }}
                            </template>
                            <!-- <template slot="created_at" scope="row">

                                {{ formatDate( row.item.user.created_at ) }}
                            </template> -->
                            <template v-for=" key in customFieldKeys " :slot=" key " scope="row">

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
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>

    import FormatsListData from '../../../mixins/FormatsListData';
    // import UserDirectory from '../../../mixins/UserDirectory'; // this mixin supports client-side pagination, it is used in one other location and should be replaced with the one i replaced it with here.. TODO
    import FormatsDates from "../../../mixins/FormatsDates";
    import Constants from '../../../mixins/Constants';

    export default {

        props: {

            customFields: {

                type     : Array,
                required : true,
            }
        },

        mixins: [ FormatsListData, FormatsDates, Constants ],

        data() {

            return {

                busy          : false,
                directoryType : 'client',
                items         : [],
                statusAliases : [],
                columns: {

                    firstname: {
                        key: 'first_name',
                        label: 'First name',
                        shouldShow: true,
                    },
                    lastname: {
                        key: 'last_name',
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
                    status_alias: {
                        key: 'status_alias',
                        label: 'Status Alias',
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
                        formatter: val => this.uppercaseWords( val )
                    },
                    created_at: {
                        key: 'date_added',
                        label: 'Date Added',
                        shouldShow: true,
                        formatter: val => this.formatDate( val )
                    },
                },
                form : new Form({

                    active          : null,
                    client_type     : '',
                    status_alias_id : '',
                    current_page    : 1,
                    json            : 1
                }),
                totalRows       : 0,
                perPage         : 100,
                customFieldKeys : []
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
                this.form.get( '/business/reports/client-directory' )
                    .then( ({ data }) => {

                        // console.log( 'data retreived: ', data );
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
            async fetchStatusAliases() {

                let response = await axios.get( '/business/status-aliases' );
                if ( response.data && response.data.client ) {

                    // console.log( response.data.client );
                    this.statusAliases = response.data.client.map( alias => { return { 'name' : alias.name, 'id' : alias.id } } );
                }
            },
            renderCell( row, field ) {

                const value = row[ field.key || field ];
                return field.formatter ? field.formatter( value ) : value;
            },
            exportExcel(){

                window.location = this.form.toQueryString( '/business/reports/client-directory?export=1' );
            },
            printTable() {

                $( '#table' ).print();
            },

            getFieldValue( meta, key ) {

                // console.log( 'meta: ', Object.entries( meta ) );
                // console.log( 'key: ', key );
                if( meta ){

                    const metaField = Object.entries( meta ).find( fieldValue => fieldValue[ 0 ] == key );
                    // console.log( 'found meta: ', metaField );
                    const {options, default: fieldDefault} = this.customFields.find( definition => definition.key == key );
                    const isDropdown = options.length > 0;

                    if( !metaField ) {

                        return fieldDefault;
                    }

                    return isDropdown ? this.getDropdownLabel( options, metaField[ 1 ] ) : metaField[ 1 ];
                }
            },

            getDropdownLabel( options, key ) {

                let option = options.find( option => option.value == key );
                return option ? option.label : '-';
            }
        },
        created(){

            this.fetch();
            // console.log( 'custom fields: ', this.customFields );

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

            // console.log( 'custom keys: ', customKeys );
            this.customFieldKeys = customKeys;

            this.columns = {

                ...this.columns,
                ...obj,
            };
        },
        async mounted(){

            await this.fetchStatusAliases();
        }
    }
</script>