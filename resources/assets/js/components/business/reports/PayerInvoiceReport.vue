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
                                v-model="form.business_id"
                                label="Office Location"
                                class="col-md-3"
                                :allow-all="false"
                        />
                        <b-form-group label="Start Date" class="col-md-2">
                            <date-picker v-model="form.start_date" name="start_date"></date-picker>
                        </b-form-group>
                        <b-form-group label="End Date" class="col-md-2">
                            <date-picker v-model="form.end_date" name="end_date"></date-picker>
                        </b-form-group>
                        <b-form-group label="Payers" class="col-md-2" :payers="payers">
                            <b-form-select v-model="form.payer_id" label="Payers" class="col-md-2" :payers="payers">
                                <option :value="null" selected>All</option>
                                <option v-for="item in payers" :key="item.id" :value="item.id">{{ item.name }}
                                </option>
                            </b-form-select>
                        </b-form-group>
                        <!--b-form-group label="Shift Type" class="col-md-2">
                            <b-form-select v-model="form.confirmed">
                                <option value="">All</option>
                                <option value="true">Confirmed</option>
                                <option value="false">Unconfirmed</option>
                            </b-form-select>
                        </b-form-group>
                        <b-form-group label="Shift Charged" class="col-md-2">
                            <b-form-select v-model="form.charged">
                                <option value="">All</option>
                                <option value="true">Charged</option>
                                <option value="false">Uncharged</option>
                            </b-form-select>
                        </b-form-group-->
                        <b-col md="2">
                            <b-form-group label="&nbsp;">
                                <b-button-group>
                                    <b-button @click="generateReport()" variant="info" :disabled="loading"><i class="fa fa-file-pdf-o mr-1"></i>Generate Report</b-button>
                                    <b-button @click="print()"><i class="fa fa-print mr-1"></i>Print</b-button>
                                </b-button-group>
                            </b-form-group>
                        </b-col>
                    </b-row>

                    <div class="d-flex justify-content-center" v-if="loading">
                        <div class="my-5">
                            <i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
                        </div>
                    </div>
                    <div v-else>
                        <b-row>
                            <b-col>
                                <b-table
                                        class="payers-invoice-table"
                                        :items="items"
                                        :fields="fields"
                                        sort-by="payer"
                                        empty-text="No Results"
                                        :busy="loading"
                                        :current-page="currentPage"
                                        :per-page="perPage"
                                />
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
    import BusinessLocationSelect from "../../business/BusinessLocationSelect";
    import BusinessLocationFormGroup from "../../business/BusinessLocationFormGroup";

    export default {
        name: "PayerInvoiceReport",
        components: {BusinessLocationFormGroup, BusinessLocationSelect},
        props: {
            payers: '',
        },
        data() {
            return {
                form: new Form({
                    start_date: moment().subtract(7, 'days').format('MM/DD/YYYY'),
                    end_date: moment().format('MM/DD/YYYY'),
                    business_id: '',
                    payer_id: null,
                    confirmed: '',
                    charged: ''
                }),

                items: [],
                payer: '',
                fields: [
                    {
                        key: 'date',
                        label: 'Date',
                        sortable: true,
                    },
                    {
                        key: 'service',
                        label: 'Service',
                        sortable: true,
                    },
                    {
                        key: 'payer',
                        label: 'Payer',
                        sortable: true,
                    },
                    {
                        key: 'client',
                        label: 'Client',
                        sortable: true,
                    },
                    {
                        key: 'caregiver',
                        label: 'Caregiver',
                        sortable: true,
                    },
                    {
                        key: 'hours',
                        label: 'Hours',
                        sortable: true,
                    },
                    {
                        key: 'units',
                        label: 'Units',
                        sortable: true,
                    },
                    {
                        key: 'rate',
                        label: 'Rate',
                        sortable: true,
                    },
                    {
                        key: 'charges',
                        label: 'Charges',
                        sortable: true,
                    },
                    {
                        key: 'due',
                        label: 'Due From Payer',
                        sortable: true,
                    },
                ],
                loading: false,
                totalRows: 0,
                perPage: 15,
                currentPage: 1,

            }
        },
        methods: {
            generateReport(){

                this.loading = true;
                this.form.get('/business/reports/payer-invoice-report?json=1')
                    .then(response => {
                        this.items=response.data;
                        this.totalRows = this.items.length;
                    })
                    .catch(() => {})
                    .finally(() => {
                        this.loading = false;
                    });

            },
            print(){
                $(".payers-invoice-table").print();
            }
        },
    }
</script>

<style scoped>

</style>