<template>

    <b-card header="Select 1 Week Range"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <b-row>

            <b-col>

                <b-alert show variant="info">View Deductibles for every Caregiver, then select individual caregivers to generate their adjustments.</b-alert>
            </b-col>
        </b-row>
        <b-row>

            <b-col md="2">

                <business-location-form-group
                    v-model=" form.businesses "
                    label="For Office Location"
                    :allow-all=" true "
                    :disabled=" form.busy "
                />
            </b-col>
            <b-col md="4">

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

            <b-col class="mb-3">

                <b-button-group>

                    <b-button @click="fetch()" variant="info" class="mr-2" :disabled=" form.busy "><i class="fa mr-1"></i>Generate Report</b-button>
                    <b-button @click="print()" :disabled=" form.busy "><i class="fa fa-print mr-1"></i>Print</b-button>
                </b-button-group>
            </b-col>
        </b-row>

        <loading-card v-if=" form.busy " text="Loading Report"></loading-card>

        <b-row>

            <b-col>

                <b-table bordered striped hover show-empty
                    :items=" items "
                    :fields=" fields "
                    :sort-by.sync=" sortBy "
                    :sort-desc.sync=" sortDesc "
                >

                <template slot="actions" scope="row">

                    <b-btn size="sm" @click=" selectCaregiver( row.item ) ">Select</b-btn>
                </template>
            </b-table>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>

    import BusinessLocationFormGroup from "../BusinessLocationFormGroup";

    export default {

        components: { BusinessLocationFormGroup },

        data () {

            return {

                items    : [],
                sortBy   : 'name',
                sortDesc : true,
                form: new Form({

                    json        : 1,
                    businesses  : '',
                    caregiver   : '',
                    client      : '',
                    client_type : '',
                    end_date    : moment().startOf( 'week' ).format( 'MM/DD/YYYY' ),
                }),
                fields: [

                    {
                        key: 'name',
                        label: 'CG Name',
                    },
                    {
                        key: 'registry',
                        label: 'Registry',
                    },
                    {
                        key: 'hours',
                        label: 'Hours Worked',
                        formatter: (val) => this.numberFormat(val)
                    },
                    {
                        key: 'deduction',
                        label: 'OccAcc Deduction',
                        formatter: (val) => this.moneyFormat(val)
                    },
                    {
                        key: 'actions',
                        label: 'Select'
                    }
                ],
            }
        },

        computed : {

            startDate(){

                return moment( this.form.end_date ).subtract( 7, 'day' ).format( 'MM/DD/YYYY' );
            }
        },

        methods: {

            selectCaregiver( caregiver ){

                console.log( 'fucking selected: ', caregiver );
            },
            async fetch() {

                this.form.get( '/business/reports/occ-acc-deductibles' )
                    .then( ({ data }) => {

                        console.log( 'the results: ', data );
                    })
                    .catch(() => {

                    })
                    .finally( () => {

                    });
            },
        },

        async mounted() {

            // this.form.start_date = moment().format('MM/DD/YYYY');
            // this.form.end_date = moment().add(30, 'day').format('MM/DD/YYYY');
        },

        watch: {

            // async 'form.businesses'(newValue, oldValue) {
            //     if (newValue != oldValue) {
            //         await this.fetchOptions();
            //     }
            // }
        },
    }
</script>
