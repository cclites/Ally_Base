<template>
    <div>
        <b-row>
            <b-col lg="12">
                <b-card
                        header="Total Charges Report"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-row>

                        <b-form-group label="Start" class="mr-2">
                            <date-picker v-model="form.start" name="date"></date-picker>
                        </b-form-group>
                        <b-form-group label="End" class="mr-2">
                            <date-picker v-model="form.end" name="date"></date-picker>
                        </b-form-group>
                        <b-col md="2">
                            <b-form-group label="&nbsp;">
                                <b-button-group>
                                    <b-button @click="fetch()" variant="info" :disabled="busy"><i class="fa fa-file-pdf-o mr-1"></i>Generate Report</b-button>
                                    <b-button @click="print()"><i class="fa fa-print mr-1"></i>Print</b-button>
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
                                        class="summary-table"
                                        :items="items"
                                        :fields="fields"
                                        :sort-by="sortBy"
                                        :empty-text="emptyText"
                                        :busy="busy"
                                        :current-page="currentPage"
                                        :per-page="perPage"
                                        :footClone="footClone"
                                >
                                    <template slot="FOOT_location" scope="item">
                                        &nbsp;
                                    </template>
                                    <template slot="FOOT_business" scope="item">
                                        &nbsp;
                                    </template>
                                    <template slot="FOOT_caregiver" scope="item">
                                        &nbsp;
                                    </template>
                                    <template slot="FOOT_system" scope="item">
                                        &nbsp;
                                    </template>
                                    <template slot="FOOT_amount" scope="item">
                                        <strong>Total: </strong> {{ moneyFormat(totals.amount) }}
                                    </template>
                                </b-table>
                            </b-col>
                        </b-row>
                    </div>

                    <b-row v-if="this.items.length > 0">
                        <b-col lg="6" >
                            <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
                        </b-col>
                        <b-col lg="6" class="text-right">
                            Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
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
        name: "TotalChargesReport",
        mixins: [FormatsNumbers],
        data() {
            return {
                form: new Form({
                    start: moment().startOf('isoweek').subtract(7, 'days').format('MM/DD/YYYY'),
                    end: moment().startOf('isoweek').subtract(1, 'days').format('MM/DD/YYYY'),
                    json: 1
                }),
                busy: false,
                totalRows: 0,
                perPage: 25,
                currentPage: 1,
                sortBy: 'location',
                sortDesc: false,
                fields: [
                    {key: 'location', label: 'Location', sortable: true,},
                    {key: 'business', label: 'To Business', sortable: true, formatter: x => { return this.moneyFormat(x) }},
                    {key: 'caregiver', label: 'To Caregiver', sortable: true, formatter: x => { return this.moneyFormat(x) }},
                    {key: 'system', label: 'To System', sortable: true, formatter: x => { return this.moneyFormat(x) }},
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
                this.form.get('/admin/reports/total_charges_report')
                    .then( ({ data }) => {
                        this.items = data.data;
                        this.totals = data.totals;
                        this.totalRows = this.items.length;
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.busy = false;
                        this.footClone = true;
                    })
            },
            print(){
                $(".summary-table").print();
            },
        },
    }
</script>
