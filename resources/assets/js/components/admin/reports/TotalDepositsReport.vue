<template>
    <div>
        <b-row>
            <b-col lg="12">
                <b-card
                        header="Total Deposits Report"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-row>
                        <b-col sm="12" md="4">

                            <b-form-group label="Date" class="mr-2">

                                <date-picker v-model=" form.startdate " name="startdate"></date-picker>
                            </b-form-group>
                        </b-col>
                        <b-col sm="12" md="4">

                            <b-form-group label="Date" class="mr-2">

                                <date-picker v-model=" form.enddate " name="enddate"></date-picker>
                            </b-form-group>
                        </b-col>
                        <b-col sm="12" md="4">

                            <b-form-group label="&nbsp;">

                                <b-button-group class="justify-content-end w-100">

                                    <b-button @click="fetch()" variant="info" :disabled="busy"><i class="fa fa-file-pdf-o mx-1"></i>Generate Report</b-button>
                                    <b-button @click="print()"><i class="fa fa-print mx-1"></i>Print</b-button>
                                </b-button-group>
                            </b-form-group>
                        </b-col>
                    </b-row>

                    <div class="d-flex justify-content-center" v-if="busy">
                        <div class="my-5">
                            <i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
                        </div>
                    </div>

                    <div v-else-if="items.length == 0">
                        {{ emptyText }}
                    </div>

                    <div v-else>
                        <b-row>
                            <b-col>
                                <b-table
                                        class="deposits-summary-table"
                                        :items="items"
                                        :fields="fields"
                                        :sort-by="sortBy"
                                        :empty-text="emptyText"
                                        :busy="busy"
                                        :footClone="footClone"
                                >
                                    <template slot="FOOT_chain" scope="item">&nbsp;
                                    </template>
                                    <template slot="FOOT_type" scope="item">
                                    </template>
                                    <template slot="FOOT_amount" scope="item">
                                        <strong>Total: </strong> {{ moneyFormat(totals.amount) }}
                                    </template>
                                </b-table>
                            </b-col>
                        </b-row>
                    </div>

                    <b-row v-if=" this.items.length > 0 ">
                        <b-col sm="12" class="text-right">
                            Showing {{ totalRows }} results
                        </b-col>
                    </b-row>

                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>

    import FormatsNumbers from '../../../mixins/FormatsNumbers';

    export default {
        name: "TotalDepositsReport",
        mixins: [ FormatsNumbers ],
        data() {
            return {
                form: new Form({
                    startdate: moment().startOf( 'isoweek' ).subtract( 1, 'days' ).format( 'MM/DD/YYYY' ),
                    enddate  : moment().endOf( 'isoday' ).format( 'MM/DD/YYYY' ),
                    json: 1
                }),
                busy: false,
                totalRows: 0,
                sortBy: 'name',
                sortDesc: false,
                fields: [
                    {key: 'name', label: 'Location', sortable: true,},
                    {key: 'amount', label: 'Total', sortable: true, formatter: x => { return this.moneyFormat(x) }},
                ],
                items: [],
                item: '',
                totals: [],
                emptyText: "No Results",
                footClone: false
            }
        },
        methods: {
            fetch() {
                this.busy = true;
                this.form.get('/admin/reports/total_deposits_report')
                    .then( ({ data }) => {
                        this.items     = data.data;
                        this.totals    = data.totals;
                        this.totalRows = this.items.length;
                    })
                    .catch( e => {} )
                    .finally(() => {
                        this.busy      = false;
                        this.footClone = true;
                    })
            },
            print(){
                $(".deposits-summary-table").print();
            },
        },
    }
</script>

<style scoped>

</style>