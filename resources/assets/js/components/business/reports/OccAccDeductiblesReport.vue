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

            <b-col class="mb-3 d-flex justify-content-end">

                <b-button-group>

                    <b-button @click="print()" :disabled=" form.busy "><i class="fa fa-print mr-2"></i>Print</b-button>
                    <b-button @click="fetch()" variant="info" :disabled=" form.busy "><i class="fa"></i>Generate Report</b-button>
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

                    <b-form-checkbox
                        :id=" `cg-checkbox-${row.item.user_id}` "
                        v-model=" row.item.selected "
                        :name=" `cg-checkbox-${row.item.user_id}` "
                        value="1"
                        unchecked-value="0"
                    ></b-form-checkbox>
                </template>
            </b-table>
            </b-col>
        </b-row>
        <b-row>

            <b-col>

                <h3>Selected CGs</h3>
                <p v-for=" ( s, i ) in selectedCaregivers " :key=" i ">{{ s.caregiver_name }}</p>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>

    import BusinessLocationFormGroup from "../BusinessLocationFormGroup";
    import FormatsNumbers from '../../../mixins/FormatsNumbers';

    export default {

        mixins : [ FormatsNumbers ],

        components: { BusinessLocationFormGroup },

        data () {

            return {

                items    : [],
                sortBy   : 'name',
                sortDesc : true,
                form: new Form({

                    json        : 1,
                    businesses  : '',
                    end_date    : moment().startOf( 'week' ).format( 'MM/DD/YYYY' ),
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
            },
            selectedCaregivers(){

                return this.items.filter( i => i.selected == "1" );
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
                        this.items = data;
                    })
                    .catch(() => {

                        this.items = [];
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
