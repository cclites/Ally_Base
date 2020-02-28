<template>

    <b-card header="Select Date Range Of When The Deductible Was Created"
        header-text-variant="white"
        header-bg-variant="info"
    >
        <b-row>

            <b-col>

                <b-alert show variant="info">View previously submitted deductibles for the location and date range provided above. The date range looks at the time the deductible was created</b-alert>
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

                            <date-picker v-model=" form.end_date " :disabled=" form.busy " />
                        </b-form-group>
                    </b-col>
                </b-row>
            </b-col>
        </b-row>
        <b-row>

            <b-col class="mb-3 d-flex justify-content-end">

                <b-button-group>

                    <b-button @click=" exportReport() " :disabled=" form.busy "><i class="fa fa-file-excel-o"></i> Export</b-button>
                    <b-button @click=" fetch() " variant="info" :disabled=" form.busy ">Generate Report</b-button>
                </b-button-group>
            </b-col>
        </b-row>
        <b-row class="mb-5">

            <b-col>

                <loading-card v-if=" form.busy" text="Loading Report"></loading-card>
                <b-table bordered striped hover show-empty
                    :items=" items "
                    :fields=" fields "
                    :sort-by.sync=" sortBy "
                    :sort-desc.sync=" sortDesc "
                    v-else
                ></b-table>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>

    import FormatsDates from '../../mixins/FormatsDates';
    import BusinessLocationFormGroup from "./BusinessLocationFormGroup";

    export default {

        components: { BusinessLocationFormGroup },

        mixins : [ FormatsDates ],

        data () {

            return {

                items    : [],
                sortBy   : 'name',
                sortDesc : true,
                form: new Form({

                    json       : 1,
                    businesses : '',
                    start_date : moment().startOf( 'week' ).format( 'MM/DD/YYYY' ),
                    end_date   : moment().endOf( 'week' ).format( 'MM/DD/YYYY' ),
                    export     : 0
                }),
                fields: [

                    {
                        key: 'id',
                        label: 'Deductible ID',
                    },
                    {
                        key: 'first_name',
                        label: 'First Name',
                    },
                    {
                        key: 'last_name',
                        label: 'Last Name',
                    },
                    {
                        key: 'week_start',
                        label: 'Premium Period Begin Date',
                        formatter: (val) => this.formatDateFromUTC( val )
                    },
                    {
                        key: 'week_end',
                        label: 'Premium Period End Date',
                        formatter: (val) => this.formatDateFromUTC( val )
                    },
                    {
                        key: 'hours_worked',
                        label: 'Hours Worked',
                    },
                    {
                        key: 'deduction_amount',
                        label: 'Deductible Amount',
                    },
                    {
                        key: 'certificate_number',
                        label: 'Certificate Number',
                    },
                ],
            }
        },

        mounted(){

        },

        computed : {

        },

        methods: {

            exportReport(){

                this.form.export = 1;
                window.open( this.form.toQueryString( '/business/occ-acc-deductibles-history' ) );
                this.form.export = 0;
            },
            fetch(){

                this.form.get( '/business/occ-acc-deductibles-history' )
                    .then( ({ data }) => {

                        this.items = Object.values( data );
                    })
                    .catch(() => {

                        this.items = [];
                    })
                    .finally( () => {

                    });
            }
        }
    }
</script>