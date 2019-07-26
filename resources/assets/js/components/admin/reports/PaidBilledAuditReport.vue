<template>
    <div>
        <b-row>
            <b-col lg="12">
                <b-card
                        header="Filters"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-row>
                        <business-location-form-group
                                v-model="form.business"
                                label="Office Location"
                                class="mr-2"
                                :allow-all="false"
                        />
                        <b-form-group label="Start Date" class="mr-2">
                            <date-picker v-model="form.start" name="start_date"></date-picker>
                        </b-form-group>
                        <b-form-group label="End Date"class="mr-2">
                            <date-picker v-model="form.end" name="end_date"></date-picker>
                        </b-form-group>
                        <b-form-group label="Salesperson" v-if="salespeople.length > 0" class="mr-2">
                            <b-form-select v-model="form.salesperson" :disabled="busy">
                                <option value="">All</option>
                                <option v-for="item in salespeople" :value="item.id">{{ item.name }}</option>
                            </b-form-select>
                        </b-form-group>
                        <b-col md="2">
                            <b-form-group label="&nbsp;">
                                <b-button-group>
                                    <b-button @click="fetch()" variant="info" :disabled="busy" title="You must select a Registry to continue"><i class="fa fa-file-pdf-o mr-1"></i>Generate Report</b-button>
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
                                    <template slot="FOOT_client" scope="item" class="primary">
                                        <strong>For Salesperson: </strong> {{ totals.salesperson }}
                                    </template>

                                    <template slot="FOOT_salesperson" scope="item">
                                        <strong>For Dates: </strong>{{ totals.start }} to {{ totals.end }}
                                    </template>

                                    <template slot="FOOT_payer" scope="item">
                                        <strong>For Client: </strong>{{ totals.client }}
                                    </template>

                                    <template slot="FOOT_amount" scope="item" class="primary">
                                        <strong>Total Amount: </strong> {{ moneyFormat(totals.amount) }}
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
    import FormatsDates from '../../../mixins/FormatsDates';
    import BusinessLocationSelect from "../../business/BusinessLocationSelect";
    import BusinessLocationFormGroup from "../../business/BusinessLocationFormGroup";

    export default {
        name: "PaidBilledAuditReport",
        mixins: [FormatsNumbers, FormatsDates],
        components: {BusinessLocationFormGroup, BusinessLocationSelect},
        data() {
            return {
                form: new Form({
                    business:'',
                    salesperson: '',
                    start: moment().startOf('isoweek').subtract(7, 'days').format('MM/DD/YYYY'),
                    end: moment().startOf('isoweek').subtract(1, 'days').format('MM/DD/YYYY'),
                    json: 1
                }),
                busy: false,
                disabled: true,
                totalRows: 0,
                perPage: 25,
                currentPage: 1,
                sortBy: 'salesperson',
                sortDesc: false,
                fields: [
                    {key: 'client', label: 'Client', sortable: true,},
                    {key: 'date', label: 'Service Date', sortable: true, formatter: x => { return this.formatDate(x) }},
                    {key: 'service', label: 'Service', sortable: true,},
                    {key: 'caregiver', label: 'Caregiver', sortable: true,},
                    {key: 'salesperson', label: 'Salesperson', sortable: true},
                    {key: 'hours', label: 'Hours', sortable: true,},
                    {key: 'billable', label: 'Total', sortable: true, formatter: x => { return this.moneyFormat(x) }},
                    {key: 'margin', label: 'Margin', sortable: true, formatter: x => { return this.moneyFormat(x) }},
                ],
                items: [],
                item: '',
                totals: [],
                salespeople: [],
                footClone: false,
                emptyText: "No Results",
            }
        },
        methods: {
            fetch() {
                this.busy = true;
                this.disabled = true;
                this.form.get('/admin/reports/paid-billed-audit-report')
                    .then( ({ data }) => {
                        this.items = data.data;
                        this.totals = data.totals;
                        this.totalRows = this.items.length;
                        this.busy = false;
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

            loadSalespeople(){
                axios.get('/business/salespersonDropdownResource?business=' + this.form.business)
                    .then( ({ data }) => {
                        this.salespeople = data;
                    })
                    .catch(e => {})
                    .finally(() => {
                    })
            },
        },

        watch: {

            async 'form.business'(newValue, oldValue) {

                if ( (!newValue || newValue != "") && newValue != oldValue) {
                    this.disabled = false;
                    this.loadSalespeople();
                }
            }
        },
    }
</script>

<style scoped>

</style>