<template>

    <b-card header="Select 1 Week Range"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <b-row v-if=" isAdmin ">

            <b-col>

                <b-alert show variant="info">View Deductibles for every Caregiver, then select individual caregivers to generate their adjustments.</b-alert>
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

                            <date-picker v-model=" startDate " :disabled=" true " :readonly=" true " />
                        </b-form-group>
                    </b-col>
                    <b-col>

                        <b-form-group label="End Date">

                            <date-picker v-model=" form.end_date " :disabled=" form.busy " />
                        </b-form-group>
                    </b-col>
                </b-row>
            </b-col>
        </b-row>
        <b-row>

            <b-col class="mb-3 d-flex justify-content-between">

                <div>
                <transition name="slide-fade" mode="out-in">

                    <b-button @click="generateDeductibles()" variant="primary" :disabled=" form.busy || generating " v-if=" selectedCaregivers.length > 0 ">Create Deposit Adjustment</b-button>
                </transition>
                </div>
                <b-button-group>

                    <b-button @click="print()" :disabled=" form.busy || generating "><i class="fa fa-print mr-2"></i>Print</b-button>
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

                generating : false,
                items      : [],
                sortBy     : 'name',
                sortDesc   : true,
                form: new Form({

                    json        : 1,
                    businesses  : '',
                    end_date    : null,
                }),
                fields: [

                    {
                        key: 'caregiver_name',
                        label: 'CG Name',
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
                ],
            }
        },

        mounted(){

            // respect the registry's start of week
            this.form.end_date = moment().day( this.officeUserSettings.calendar_week_start ).format( 'MM/DD/YYYY' );
        },

        computed : {

            computedFields(){

                const fields = this.fields;
                if( this.isAdmin ){

                    fields.push({
                        key: 'actions',
                        label: 'Select'
                    });
                }
                return fields;
            },
            startDate(){

                return moment( this.form.end_date ).subtract( 7, 'day' ).format( 'MM/DD/YYYY' );
            },
            selectedCaregivers(){

                let shit = this.items.filter( i => i.selected == 1 );
                return shit;
            }
        },

        methods: {

            async fetch() {

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
                        'amount'       : c.deduction
                    }
                });

                let form = new Form( inputs );
                form.post( '/business/occ-acc-deductibles' )
                    .then( ( data ) => {

                        // this.selectedCaregivers.forEach( c => {
                        //     // this may not be necessaery.. leaving it here just in case

                        //     const index = this.items.findIndex( i => i.user_id == c.user_id );
                        //     this.items.splice( index, 1 );
                        // });
                    })
                    .catch( err => {})
                    .finally( () => this.generating = false );
            }
        }
    }
</script>
