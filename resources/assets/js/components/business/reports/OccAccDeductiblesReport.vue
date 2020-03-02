<template>

    <b-card header="Select 1 Week Range"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <b-row v-if=" isAdmin ">

            <b-col>

                <b-alert show variant="info">View Deductibles for every Caregiver, then select individual caregivers to generate their adjustments. You are only seeing this because you are an admin</b-alert>
            </b-col>
        </b-row>
        <b-row>

            <b-col md="3">

                <business-location-form-group
                    v-model=" form.businesses "
                    label="For Office Location"
                    :allow-all=" true "
                    :disabled=" form.busy "
                />
            </b-col>
            <b-col md="6">

                <b-row>

                    <b-col>

                        <b-form-group label="Start Date">

                            <date-picker v-model=" form.start_date " :disabled=" form.busy " />
                        </b-form-group>
                    </b-col>
                    <b-col>

                        <b-form-group label="End Date">

                            <date-picker v-model=" endDate " :disabled=" true " :readonly=" true " />
                        </b-form-group>
                    </b-col>
                </b-row>
            </b-col>
        </b-row>

        <b-row>

            <b-col class="mb-3 d-flex justify-content-between">

                <div>
                <transition name="slide-fade" mode="out-in">

                    <b-button @click="generateDeductibles()" variant="primary" :disabled=" form.busy || generating " v-if=" isAdmin && selectedCaregivers.length > 0 ">Create Deposit Adjustments</b-button>
                </transition>
                </div>
                <b-button-group>

                    <b-button @click="selectAll()" :disabled=" form.busy || generating ">Select All</b-button>
                    <b-button @click="fetch()" variant="info" :disabled=" form.busy || generating ">Generate Report</b-button>
                </b-button-group>
            </b-col>
        </b-row>

        <b-row>

            <b-col>

                <loading-card v-if=" form.busy " text="Loading Report"></loading-card>
                <b-table bordered striped hover show-empty
                    :items=" items "
                    :fields=" computedFields "
                    :sort-by.sync=" sortBy "
                    :sort-desc.sync=" sortDesc "
                    v-else
                >

                    <template slot="actions" scope="row">

                        <b-form-checkbox
                            :id=" `cg-checkbox-${row.item.user_id}` "
                            v-model=" row.item.selected "
                            @click.native=" selectCaregiver( row.item.user_id, row.item.selected ) "
                            :name=" `cg-checkbox-${row.item.user_id}` "
                            :value=" 1 "
                            :unchecked-value=" 0 "
                        ></b-form-checkbox>
                    </template>
                </b-table>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>

    import BusinessLocationFormGroup from "../BusinessLocationFormGroup";
    import FormatsNumbers from '../../../mixins/FormatsNumbers';
    import Constants from '../../../mixins/Constants';

    export default {

        mixins : [ FormatsNumbers, Constants ],

        components: { BusinessLocationFormGroup },

        data () {

            return {

                generating         : false,
                items              : [],
                sortBy             : 'name',
                sortDesc           : true,
                selectedCaregivers : [],
                form: new Form({

                    json       : 1,
                    businesses : '',
                    start_date : moment().startOf( 'week' ).format( 'MM/DD/YYYY' ),
                    export     : 0,
                }),
                fields: [

                    {
                        key: 'caregiver_name',
                        label: 'Caregiver Name',
                    },
                    {
                        key: 'registry',
                        label: 'Registry',
                    },
                    {
                        key: 'duration',
                        label: 'Hours Worked',
                        // formatter: (val) => this.numberFormat(val)
                    },
                    {
                        key: 'deduction',
                        label: 'OccAcc Deduction',
                        formatter: (val) => this.moneyFormat(val)
                    }
                ]
            }
        },

        mounted(){

            // respect the registry's start of week, unless an admin
            if( !this.isAdmin ) this.form.start_date = moment().day( this.officeUserSettings.calendar_week_start ).format( 'MM/DD/YYYY' );
        },

        computed : {

            computedFields(){

                const fields = this.fields;
                if( this.isAdmin ){

                    fields.push({

                        key   : 'actions',
                        label : 'Select'
                    });
                }
                return fields;
            },
            endDate(){

                return moment( this.form.start_date ).add( 6, 'day' ).format( 'MM/DD/YYYY' );
            }
        },

        methods: {

            selectAll(){

                if( this.selectedCaregivers.length == this.items.length ){

                    this.items.forEach( item => {

                        item.selected = 0;
                    });
                } else {

                    this.items.forEach( item => {

                        item.selected = 1;
                    });
                }

                this.selectedCaregivers = this.items.filter( i => i.selected == 1 );
            },
            selectCaregiver( user_id, value ){

                this.items.find( i => i.user_id == user_id ).selected = value;
                this.selectedCaregivers = this.items.filter( i => i.selected == 1 );
            },
            async fetch() {

                // console.log( moment( this.form.start_date ).format( 'd' ), this.officeUserSettings.calendar_week_start );
                this.selectedCaregivers = [];

                if( !this.isAdmin && moment( this.form.start_date ).format( 'd' ) != this.officeUserSettings.calendar_week_start ){

                    alerts.addMessage( 'error', `You must select a week starting with ${this.CALENDAR_START_OF_WEEK[ this.officeUserSettings.calendar_week_start ]}` );
                    return;
                }

                this.form.get( '/business/occ-acc-deductibles' )
                    .then( ({ data }) => {

                        this.items = Object.values( data );
                    })
                    .catch(() => {

                        this.items = [];
                    })
                    .finally( () => {

                    });
            },
            generateDeductibles(){

                this.generating = true;

                const inputs = this.selectedCaregivers.map( c => {

                    return {

                        'caregiver_id' : c.user_id,
                        'start_date'   : this.form.start_date,
                        'end_date'     : this.endDate,
                        'businesses'   : c.registry_id.split( ', ' )
                    }
                });

                let form = new Form( inputs );
                form.post( '/business/occ-acc-deductibles' )
                    .then( ( data ) => {

                        this.selectedCaregivers = [];
                        this.fetch(); // this will automatically clear all the items that were just used
                    })
                    .catch( err => {})
                    .finally( () => this.generating = false );
            }
        }
    }
</script>
